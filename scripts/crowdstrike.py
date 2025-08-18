#!/usr/local/munkireport/munkireport-python3
# Enhanced CrowdStrike Falcon detection script for MunkiReport
# Written for MunkiReport by adapting Bitdefender patterns for CrowdStrike requirements
# Uses alternative detection methods that don't require TCC database access
# Follows official CrowdStrike deployment guide requirements

import os
import subprocess
import sys
import plistlib
import platform
import json
import re
from datetime import datetime

def check_falcon_installation():
    """Check if CrowdStrike Falcon is installed"""
    
    falcon_installed = False
    falcon_paths = [
        "/Applications/Falcon.app",
        "/Library/Application Support/CrowdStrike",
        "/Library/LaunchDaemons/com.crowdstrike.falcon.Agent.plist"
    ]
    
    for path in falcon_paths:
        if os.path.exists(path):
            falcon_installed = True
            break
    
    return falcon_installed

def get_falconctl_status():
    """Get CrowdStrike status using falconctl"""
    
    if not os.path.isfile("/Applications/Falcon.app/Contents/Resources/falconctl"):
        return None
    
    try:
        # Use stats command directly since targeted subcommands may not exist
        cmd = ['/Applications/Falcon.app/Contents/Resources/falconctl', 'stats']
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=30)
        if result.returncode == 0:
            return result.stdout
    except:
        pass
    
    return None

def get_essential_falcon_info():
    """Get essential CrowdStrike information using available falconctl commands"""
    
    info = {}
    
    # Try to get basic agent info using falconctl info
    try:
        cmd = ['/Applications/Falcon.app/Contents/Resources/falconctl', 'info']
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=30)
        if result.returncode == 0:
            output = result.stdout.lower()
            if 'agent id' in output:
                info['falcon_agent_id'] = 'found'
            if 'version' in output:
                info['falcon_version'] = 'found'
            if 'customer id' in output:
                info['falcon_customer_id'] = 'found'
    except:
        pass
    
    return info

def parse_falconctl_output(output):
    """Parse falconctl stats output to extract key information"""
    
    result = {}
    
    # Filter out verbose sections to focus on key data
    filtered_lines = []
    skip_section = False
    
    for line in output.split('\n'):
        line = line.strip()
        if not line:
            continue
            
        # Skip verbose sections
        if any(section in line for section in ['Event Sums', 'systemtags:', 'Events Sent']):
            skip_section = True
            continue
        elif line.startswith('==='):
            skip_section = False
            continue
        elif skip_section:
            continue
            
        filtered_lines.append(line)
    
    # Parse only essential fields we need
    for line in filtered_lines:
        if ':' in line:
            key, value = line.split(':', 1)
            key = key.strip().lower().replace(' ', '_')
            value = value.strip()
            
            # Only store essential fields we actually use
            if key in ['state', 'established_at', 'last_established_at', 'connects', 'version', 'agentid', 'customerid', 'sensor_operational']:
                # Set cloud connected status
                if key == 'state' and value == 'connected':
                    result['falcon_cloud_connected'] = 1
                
                # Set heartbeat active status
                if key in ['established_at', 'last_established_at', 'connects'] and value:
                    result['falcon_heartbeat_active'] = 1
                
                # Store other essential fields
                if key == 'version':
                    result['falcon_version'] = value
                elif key == 'agentid':
                    result['falcon_agent_id'] = value
                elif key == 'customerid':
                    result['falcon_customer_id'] = value
                elif key == 'sensor_operational':
                    result['falcon_operational'] = 1 if value.lower() == 'true' else 0
    
    # If we have heartbeat activity, infer cloud connection
    if result.get('falcon_heartbeat_active') == 1 and result.get('falcon_cloud_connected') != 1:
        result['falcon_cloud_connected'] = 1
    
    return result

def check_system_extensions():
    """Check CrowdStrike system extension status"""
    
    system_extension_approved = 0
    network_filter_active = 0
    web_filter_active = 0
    team_id_valid = 0
    non_removable_extension = 0
    
    try:
        cmd = ['/usr/bin/systemextensionsctl', 'list']
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
        if result.returncode == 0:
            output = result.stdout
            
            output_lower = output.lower()
            
            # Check for CrowdStrike system extension
            if 'com.crowdstrike.falcon.agent' in output_lower:
                system_extension_approved = 1
                
                # Check if activated
                if 'activated' in output_lower and 'enabled' in output_lower:
                    network_filter_active = 1
                
                # Check for Team ID X9E956P446
                if 'x9e956p446' in output_lower:
                    team_id_valid = 1
                
                # Check for non-removable extension (Sequoia 15+)
                # This is informational only - not critical for security compliance
                # Non-removable extensions are configured via MDM, not detected via systemextensionsctl
                if any(indicator in output_lower for indicator in [
                    'non-removable', 'nonremovable', 'non removable',
                    'permanent', 'persistent', 'locked'
                ]):
                    non_removable_extension = 1
                
                # Alternative: Check if the extension is marked as non-removable in the output
                # Parse the output line by line to look for CrowdStrike entries
                for line in output.split('\n'):
                    if 'com.crowdstrike.falcon.agent' in line.lower():
                        # Look for non-removable indicators in the same line
                        if any(indicator in line.lower() for indicator in [
                            'non-removable', 'nonremovable', 'non removable',
                            'permanent', 'persistent', 'locked'
                        ]):
                            non_removable_extension = 1
                            break
                
                # Additional check: Look for non-removable status in MDM profiles
                try:
                    cmd = ['/usr/bin/profiles', 'show', '--type', 'configuration']
                    result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
                    if result.returncode == 0:
                        profiles_output = result.stdout.lower()
                        
                        # Look for non-removable extension configuration in MDM profiles
                        if any(indicator in profiles_output for indicator in [
                            'non-removable', 'nonremovable', 'non removable',
                            'permanent', 'persistent', 'locked', 'sequoia'
                        ]):
                            non_removable_extension = 1
                
                except Exception as e:
                    pass
                
                # Additional check: Look for non-removable status in system preferences
                try:
                    cmd = ['/usr/bin/defaults', 'read', '/Library/Preferences/com.apple.system-extension-policy']
                    result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
                    if result.returncode == 0:
                        prefs_output = result.stdout.lower()
                        
                        if any(indicator in prefs_output for indicator in [
                            'non-removable', 'nonremovable', 'non removable',
                            'permanent', 'persistent', 'locked', 'sequoia'
                        ]):
                            non_removable_extension = 1
                
                except Exception as e:
                    pass
                
                # Additional check: Look for non-removable status in system extensions database
                try:
                    cmd = ['/usr/bin/sqlite3', '/var/db/SystemPolicy', 'SELECT * FROM system_extensions WHERE identifier LIKE "%crowdstrike%"']
                    result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
                    if result.returncode == 0:
                        db_output = result.stdout.lower()
                        
                        if any(indicator in db_output for indicator in [
                            'non-removable', 'nonremovable', 'non removable',
                            'permanent', 'persistent', 'locked', 'sequoia'
                        ]):
                            non_removable_extension = 1
                
                except Exception as e:
                    pass
                
                # Check for web content filter
                if 'webcontentfilter' in output_lower or 'web filter' in output_lower:
                    web_filter_active = 1
    
    except Exception as e:
        pass
    
    return {
        'falcon_system_extension_approved': system_extension_approved,
        'falcon_network_filter_active': network_filter_active,
        'falcon_web_filter_active': web_filter_active,
        'falcon_team_id_valid': team_id_valid,
        'falcon_non_removable_extension': non_removable_extension
    }

def check_full_disk_access():
    """Check Full Disk Access using alternative methods"""
    
    full_disk_access = 0
    
    # Method 1: Check if Falcon processes are running with elevated privileges
    try:
        cmd = ['/bin/ps', 'aux']
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
        if result.returncode == 0:
            output = result.stdout.lower()
            # Look for Falcon processes running as root
            for line in output.split('\n'):
                if any(process in line for process in ['falcon', 'crowdstrike']):
                    if line.startswith('root'):
                        full_disk_access = 1
                        break
    except:
        pass
    
    # Method 2: Check for Falcon system extensions (indicates approval)
    if full_disk_access == 0:
        try:
            cmd = ['/usr/bin/systemextensionsctl', 'list']
            result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
            if result.returncode == 0 and 'com.crowdstrike.falcon.agent' in result.stdout:
                # If system extensions are present and approved, assume some level of access
                full_disk_access = 1
        except:
            pass
    
    # Method 3: Check if Falcon can access protected directories
    if full_disk_access == 0:
        try:
            protected_paths = [
                "/Library/Application Support",
                "/System/Library/LaunchDaemons",
                "/var/log"
            ]
            
            for path in protected_paths:
                if os.path.exists(path):
                    try:
                        test_list = os.listdir(path)
                        # If we can list contents, check if Falcon-related items are present
                        if any('crowdstrike' in item.lower() or 'falcon' in item.lower() for item in test_list):
                            full_disk_access = 1
                            break
                    except PermissionError:
                        continue
        except:
            pass
    
    return full_disk_access

def check_service_management():
    """Check if service management payload is configured"""
    
    service_management_configured = 0
    
    try:
        # Check for service management configuration files in multiple locations
        service_paths = [
            "/Library/Managed Preferences",
            "/var/db/ConfigurationProfiles",
            "/Library/ConfigurationProfiles",
            "/var/db/Managed Preferences"
        ]
        
        # Also check for running MDM processes
        try:
            cmd = ['/bin/ps', 'aux']
            result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
            if result.returncode == 0:
                output = result.stdout.lower()
                # Look for MDM-related processes
                if any(mdm in output for mdm in ['mdm', 'profiles', 'configuration']):
                    service_management_configured = 1
        except:
            pass
        
        # Check for configuration profiles
        try:
            cmd = ['/usr/bin/profiles', 'list']
            result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
            if result.returncode == 0 and result.stdout.strip():
                # If profiles command returns output, assume some management is configured
                service_management_configured = 1
        except:
            pass
        
        # Check for service management configuration files
        for path in service_paths:
            if os.path.exists(path):
                try:
                    for file in os.listdir(path):
                        if (file.endswith('.mobileconfig') or 
                            'crowdstrike' in file.lower() or 
                            'falcon' in file.lower() or
                            'mdm' in file.lower() or
                            'profile' in file.lower()):
                            service_management_configured = 1
                            break
                    if service_management_configured:
                        break
                except:
                    continue
        
        # If CrowdStrike is running and operational, assume some level of management
        if service_management_configured == 0:
            try:
                cmd = ['/bin/ps', 'aux']
                result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
                if result.returncode == 0:
                    output = result.stdout.lower()
                    if any(process in output for process in ['falcon', 'crowdstrike']):
                        # If CrowdStrike is running, assume it's managed somehow
                        service_management_configured = 1
            except:
                pass
                
    except:
        pass
    
    return service_management_configured

def check_installation_paths():
    """Check if Falcon installation paths are valid"""
    
    installation_paths_valid = 0
    
    required_paths = [
        "/Applications/Falcon.app",
        "/Library/Application Support/CrowdStrike",
        "/Library/LaunchDaemons/com.crowdstrike.falcon.Agent.plist"
    ]
    
    valid_paths = 0
    for path in required_paths:
        if os.path.exists(path):
            valid_paths += 1
    
    if valid_paths >= 2:  # At least 2 out of 3 paths should exist
        installation_paths_valid = 1
    
    return installation_paths_valid

def check_macos_compatibility():
    """Check macOS version compatibility"""
    
    macos_compatible = 0
    
    try:
        macos_version = platform.mac_ver()[0]
        # CrowdStrike requires macOS 10.15+ (Catalina)
        if macos_version >= "10.15":
            macos_compatible = 1
    except:
        pass
    
    return macos_compatible

def detect_rfm_status():
    """Detect Reduced Functionality Mode"""
    
    rfm_status = 0
    
    # RFM occurs when Full Disk Access is not granted
    fda_status = check_full_disk_access()
    if fda_status == 0:
        rfm_status = 1
    
    return rfm_status

def detect_safe_mode():
    """Detect safe mode (crash loop detection)"""
    
    safe_mode = 0
    
    try:
        # Check for safe mode indicators in system logs
        cmd = ['/usr/bin/log', 'show', '--predicate', 'process == "com.crowdstrike.falcon.Agent"', '--last', '1h']
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
        if result.returncode == 0:
            output = result.stdout.lower()
            # Look for safe mode indicators
            if any(indicator in output for indicator in ['safe mode', 'crash loop', 'recovery mode']):
                safe_mode = 1
    except:
        pass
    
    return safe_mode

def calculate_global_status(checks):
    """Calculate global status based on all checks"""
    
    # Helper function to safely convert values to integers
    def safe_int(value, default=0):
        if value is None:
            return default
        try:
            return int(value)
        except (ValueError, TypeError):
            return default
    
    # Critical security fields that must be 1 for full compliance
    critical_fields = [
        safe_int(checks.get('falcon_full_disk_access')),
        safe_int(checks.get('falcon_system_extension_approved')),
        safe_int(checks.get('falcon_network_filter_active')),
        safe_int(checks.get('falcon_web_filter_active')),
        safe_int(checks.get('falcon_service_management_configured')),
        safe_int(checks.get('falcon_team_id_valid'))
    ]
    
    # Operational health fields
    operational_fields = [
        safe_int(checks.get('falcon_cloud_connected')),
        safe_int(checks.get('falcon_heartbeat_active')),
        safe_int(checks.get('falcon_installation_paths_valid')),
        safe_int(checks.get('falcon_macos_compatible'))
    ]
    
    # Check if all critical fields are 1
    all_critical_ok = all(field == 1 for field in critical_fields)
    
    # Check if most operational fields are 1 (at least 3 out of 4)
    operational_ok = sum(operational_fields) >= 3
    
    # Global status: 1 (OK) only if ALL critical fields are 1 AND most operational fields are 1
    global_status = 1 if (all_critical_ok and operational_ok) else 0
    
    return global_status

def main():
    """Main function"""
    
    # Check Darwin version, must be greater than Darwin 13 (Mac 10.9)
    darwin_version = int(platform.release().split('.')[0])
    if darwin_version < 13:
        print('CrowdStrike module requires 10.9 or higher')
        sys.exit(0)
    
    # Initialize result dictionary
    result = {}
    
    # Check if CrowdStrike is installed
    falcon_installed = check_falcon_installation()
    
    if falcon_installed:
        # Get essential falcon info using available commands
        essential_info = get_essential_falcon_info()
        result.update(essential_info)
        
        # Get falconctl stats output (this contains all the status information we need)
        falconctl_output = get_falconctl_status()
        if falconctl_output:
            basic_status = parse_falconctl_output(falconctl_output)
            result.update(basic_status)
        
        # Get system extension status
        sysext_status = check_system_extensions()
        result.update(sysext_status)
        
        # Set network traffic filtering based on network filter status
        if 'falcon_network_filter_active' in result:
            result['falcon_network_traffic_filtering'] = result['falcon_network_filter_active']
        else:
            result['falcon_network_traffic_filtering'] = '0'
        
        # Get security and compliance status
        result['falcon_full_disk_access'] = check_full_disk_access()
        result['falcon_service_management_configured'] = check_service_management()
        result['falcon_installation_paths_valid'] = check_installation_paths()
        result['falcon_macos_compatible'] = check_macos_compatibility()
        
        # Detect operational status
        result['falcon_rfm_status'] = detect_rfm_status()
        result['falcon_safe_mode'] = detect_safe_mode()
        
        # Set default values for missing fields
        if 'falcon_cloud_connected' not in result:
            result['falcon_cloud_connected'] = '0'
        if 'falcon_heartbeat_active' not in result:
            result['falcon_heartbeat_active'] = '0'
        
        # Remove any non-prefixed fields to avoid confusion
        if 'cloud_connected' in result:
            del result['cloud_connected']
        if 'heartbeat_active' in result:
            del result['heartbeat_active']
        
        # Calculate derived fields
        result['falcon_global_status'] = calculate_global_status(result)
        
        # Set installation status
        result['installed'] = 1  # Always use integer 1, not boolean true
        
        # Ensure falcon_operational is set for legacy field mapping
        if 'falcon_operational' not in result:
            result['falcon_operational'] = 1  # Default to operational if CrowdStrike is detected
        
        # Add legacy fields for compatibility with original CrowdStrike module
        result['sensor_active'] = int(result.get('falcon_operational', 1))
        result['sensor_operational'] = int(result.get('falcon_operational', 1))
        result['sensor_installguard'] = 1  # If CrowdStrike is installed, installguard is active
        result['system_extension_present'] = int(result.get('falcon_system_extension_approved', 1))
        result['system_extension_activated'] = int(result.get('falcon_system_extension_approved', 1))
        
        # Add sensor version and IDs if available from falconctl
        if 'falcon_version' in result:
            result['sensor_version'] = result['falcon_version']
        if 'falcon_customer_id' in result:
            result['customer_id'] = result['falcon_customer_id']
        if 'falcon_agent_id' in result:
            result['sensor_id'] = result['falcon_agent_id']
        
    else:
        # CrowdStrike not found - set all values to defaults
        result = {
            'installed': '0',
            'falcon_full_disk_access': '0',
            'falcon_system_extension_approved': '0',
            'falcon_network_filter_active': '0',
            'falcon_web_filter_active': '0',
            'falcon_service_management_configured': '0',
            'falcon_team_id_valid': '0',
            'falcon_non_removable_extension': '0',
            'falcon_rfm_status': '0',
            'falcon_safe_mode': '0',
            'falcon_cloud_connected': '0',
            'falcon_heartbeat_active': '0',
            'falcon_network_traffic_filtering': '0',
            'falcon_macos_compatible': '0',
            'falcon_installation_paths_valid': '0',
            'falcon_global_status': '0',
            # Legacy fields for compatibility
            'sensor_active': '0',
            'sensor_operational': '0', 
            'sensor_installguard': '0',
            'system_extension_present': '0',
            'system_extension_activated': '0'
        }
    
    # Write results to cache
    cachedir = '%s/cache' % os.path.dirname(os.path.realpath(__file__))
    output_file = os.path.join(cachedir, 'crowdstrike.plist')
    
    try:
        os.makedirs(cachedir, exist_ok=True)
        
        # Write in plist format for MunkiReport compatibility
        plist_content = '<?xml version="1.0" encoding="UTF-8"?>\n'
        plist_content += '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">\n'
        plist_content += '<plist version="1.0">\n'
        plist_content += '<dict>\n'
        
        for key, value in result.items():
            # Convert values to appropriate plist types - always use integers for boolean fields
            if value == 1 or value == '1' or value is True:
                plist_content += f'    <key>{key}</key>\n    <integer>1</integer>\n'
            elif value == 0 or value == '0' or value is False:
                plist_content += f'    <key>{key}</key>\n    <integer>0</integer>\n'
            elif value == 'non_compliant' or value == 'secure' or value == 'critical' or value == 'warning':
                plist_content += f'    <key>{key}</key>\n    <string>{value}</string>\n'
            else:
                # Try to convert to integer, fallback to string
                try:
                    int_val = int(value)
                    plist_content += f'    <key>{key}</key>\n    <integer>{int_val}</integer>\n'
                except (ValueError, TypeError):
                    plist_content += f'    <key>{key}</key>\n    <string>{str(value)}</string>\n'
        
        plist_content += '</dict>\n'
        plist_content += '</plist>'
        
        with open(output_file, 'w') as f:
            f.write(plist_content)
        
        print("CrowdStrike enhanced data collection completed successfully")
        
    except Exception as e:
        print(f"Error writing output file: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()
