<div id="crowdstrike-tab"></div>
<div id="lister" style="font-size: large; float: right;">
    <a href="/show/listing/crowdstrike/crowdstrike" title="List">
        <i class="btn btn-default tab-btn fa fa-list"></i>
    </a>
</div>
<div id="report_btn" style="font-size: large; float: right;">
    <a href="/show/report/crowdstrike/crowdstrike" title="Report">
        <i class="btn btn-default tab-btn fa fa-th"></i>
    </a>
</div>
<h2><i class="fa fa-shield"></i> <span data-i18n="crowdstrike.title"></span> <span id="crowdstrike-cnt" class="badge"></span></h2>

<div id="crowdstrike-msg" class="text-center">Loading CrowdStrike data...</div>

<table id="crowdstrike-tab-table" style="max-width:750px; display: none;"><tbody></tbody></table>

<script>
$(document).on('appReady', function(){
    
    // Get the data using standard approach
    $.getJSON(appUrl + '/module/crowdstrike/get_data/' + serialNumber, function(data){
        
        var table = $('#crowdstrike-tab-table');
        
        // Update the menu badge based on sensor_active status
        $('#crowdstrike-cnt').text(data.sensor_active == 1 ? 'Yes' : 'No');
        
        // Check if we have data
        if (data && Object.keys(data).length > 0) {
            
            // Hide loading message and show table
            $('#crowdstrike-msg').text('');
            $('#crowdstrike-tab-table').show();
            
            // Generate rows from data
            var rows = '';
            
            // Global Status (most important field)
            if (data.falcon_global_status !== undefined && data.falcon_global_status !== null) {
                rows += '<tr><th data-i18n="crowdstrike.global_status">Global Status</th><td>' + (data.falcon_global_status ? '<span class="label label-success">OK</span>' : '<span class="label label-danger">Issues</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.global_status">Global Status</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Sensor Active
            rows += '<tr><th data-i18n="crowdstrike.sensor_active">Sensor Active</th><td>' + (data.sensor_active ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            
            // Cloud Connected
            if (data.falcon_cloud_connected !== undefined && data.falcon_cloud_connected !== null) {
                rows += '<tr><th data-i18n="crowdstrike.cloud_connected">Cloud Connected</th><td>' + (data.falcon_cloud_connected ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.cloud_connected">Cloud Connected</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Heartbeat Active
            if (data.falcon_heartbeat_active !== undefined && data.falcon_heartbeat_active !== null) {
                rows += '<tr><th data-i18n="crowdstrike.heartbeat_active">Heartbeat Active</th><td>' + (data.falcon_heartbeat_active ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.heartbeat_active">Heartbeat Active</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // RFM Status
            if (data.falcon_rfm_status !== undefined && data.falcon_rfm_status !== null) {
                rows += '<tr><th data-i18n="crowdstrike.rfm_status">RFM Status</th><td>' + (data.falcon_rfm_status ? '<span class="label label-danger">Yes (RFM Mode)</span>' : '<span class="label label-success">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.rfm_status">RFM Status</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Safe Mode
            if (data.falcon_safe_mode !== undefined && data.falcon_safe_mode !== null) {
                rows += '<tr><th data-i18n="crowdstrike.safe_mode">Safe Mode</th><td>' + (data.falcon_safe_mode ? '<span class="label label-warning">Yes</span>' : '<span class="label label-success">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.safe_mode">Safe Mode</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // System Extension Present
            rows += '<tr><th data-i18n="crowdstrike.system_extension_present">System Extension Present</th><td>' + (data.system_extension_present ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            
            // System Extension Activated
            rows += '<tr><th data-i18n="crowdstrike.system_extension_activated">System Extension Activated</th><td>' + (data.system_extension_activated ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            
            // Service Management
            if (data.falcon_service_management_configured !== undefined && data.falcon_service_management_configured !== null) {
                rows += '<tr><th data-i18n="crowdstrike.service_management">Service Management</th><td>' + (data.falcon_service_management_configured ? '<span class="label label-success">Configured</span>' : '<span class="label label-danger">Not Configured</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.service_management">Service Management</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Network Filter Active
            if (data.falcon_network_filter_active !== undefined && data.falcon_network_filter_active !== null) {
                rows += '<tr><th data-i18n="crowdstrike.network_filter_active">Network Filter Active</th><td>' + (data.falcon_network_filter_active ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.network_filter_active">Network Filter Active</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Web Filter Active
            if (data.falcon_web_filter_active !== undefined && data.falcon_web_filter_active !== null) {
                rows += '<tr><th data-i18n="crowdstrike.web_filter_active">Web Filter Active</th><td>' + (data.falcon_web_filter_active ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.web_filter_active">Web Filter Active</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Uninstall Protection
            rows += '<tr><th data-i18n="crowdstrike.sensor_installguard">Uninstall Protection</th><td>' + (data.sensor_installguard ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            
            // Installation Paths Valid
            if (data.falcon_installation_paths_valid !== undefined && data.falcon_installation_paths_valid !== null) {
                rows += '<tr><th data-i18n="crowdstrike.installation_paths_valid">Installation Paths Valid</th><td>' + (data.falcon_installation_paths_valid ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.installation_paths_valid">Installation Paths Valid</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // macOS Compatible
            if (data.falcon_macos_compatible !== undefined && data.falcon_macos_compatible !== null) {
                rows += '<tr><th data-i18n="crowdstrike.macos_compatible">macOS Compatible</th><td>' + (data.falcon_macos_compatible ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.macos_compatible">macOS Compatible</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Network Traffic Filtering
            if (data.falcon_network_traffic_filtering !== undefined && data.falcon_network_traffic_filtering !== null) {
                rows += '<tr><th data-i18n="crowdstrike.network_traffic_filtering">Network Traffic Filtering</th><td>' + (data.falcon_network_traffic_filtering ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.network_traffic_filtering">Network Traffic Filtering</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Non-Removable Extension
            if (data.falcon_non_removable_extension !== undefined && data.falcon_non_removable_extension !== null) {
                rows += '<tr><th data-i18n="crowdstrike.non_removable_extension">Non-Removable Extension</th><td>' + (data.falcon_non_removable_extension ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.non_removable_extension">Non-Removable Extension</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // System Extension Approved
            if (data.falcon_system_extension_approved !== undefined && data.falcon_system_extension_approved !== null) {
                rows += '<tr><th data-i18n="crowdstrike.system_extension_approved">System Extension Approved</th><td>' + (data.falcon_system_extension_approved ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.system_extension_approved">System Extension Approved</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Full Disk Access
            if (data.falcon_full_disk_access !== undefined && data.falcon_full_disk_access !== null) {
                rows += '<tr><th data-i18n="crowdstrike.full_disk_access">Full Disk Access</th><td>' + (data.falcon_full_disk_access ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.full_disk_access">Full Disk Access</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Team ID Valid
            if (data.falcon_team_id_valid !== undefined && data.falcon_team_id_valid !== null) {
                rows += '<tr><th data-i18n="crowdstrike.team_id_valid">Team ID Valid</th><td>' + (data.falcon_team_id_valid ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>') + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.team_id_valid">Team ID Valid</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            
            // Basic sensor information (at the end)
            if (data.sensor_id) {
                rows += '<tr><th data-i18n="crowdstrike.sensor_id">Sensor ID</th><td>' + data.sensor_id + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.sensor_id">Sensor ID</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            if (data.sensor_version) {
                rows += '<tr><th data-i18n="crowdstrike.sensor_version">Sensor Version</th><td>' + data.sensor_version + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.sensor_version">Sensor Version</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            if (data.customer_id) {
                rows += '<tr><th data-i18n="crowdstrike.customer_id">Customer ID</th><td>' + data.customer_id + '</td></tr>';
            } else {
                rows += '<tr><th data-i18n="crowdstrike.customer_id">Customer ID</th><td><span class="label label-warning">Unknown</span></td></tr>';
            }
            

            
            // Add rows to table
            table.find('tbody').html(rows);
            
        } else {
            // No data available
            $('#crowdstrike-msg').text('No CrowdStrike data available');
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        // Handle errors
        $('#crowdstrike-msg').text('Error loading CrowdStrike data: ' + textStatus);
    });
});
</script>
