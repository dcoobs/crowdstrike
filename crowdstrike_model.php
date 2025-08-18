<?php

use CFPropertyList\CFPropertyList;
use Illuminate\Database\Capsule\Manager as Capsule;

class Crowdstrike_model extends \Model {

	public function __construct($serial='')
	{
		parent::__construct('id', 'crowdstrike'); //primary key, tablename
		$this->rs['id'] = '';
		$this->rs['serial_number'] = $serial; $this->rt['serial_number'] = 'VARCHAR(255) UNIQUE';
        
        // Existing core fields
        $this->rs['sensor_id'] = '';
        $this->rs['sensor_version'] = '';
        $this->rs['customer_id'] = '';
        $this->rs['sensor_operational'] = '';
        $this->rs['sensor_installguard'] = 0; //boolean
        $this->rs['sensor_active'] = 0; //boolean
        $this->rs['system_extension_present'] = 0; //boolean
        $this->rs['system_extension_activated'] = 0; //boolean
        $this->rs['installed'] = null; // tri-state: null legacy, 1 installed, 0 not installed

        // Enhanced security and compliance fields
        $this->rs['falcon_full_disk_access'] = null;
        $this->rs['falcon_system_extension_approved'] = null;
        $this->rs['falcon_network_filter_active'] = null;
        $this->rs['falcon_web_filter_active'] = null;
        $this->rs['falcon_service_management_configured'] = null;
        $this->rs['falcon_team_id_valid'] = null;
        $this->rs['falcon_non_removable_extension'] = null;
        $this->rs['falcon_rfm_status'] = null;
        $this->rs['falcon_safe_mode'] = null;
        $this->rs['falcon_cloud_connected'] = null;
        $this->rs['falcon_heartbeat_active'] = null;
        $this->rs['falcon_network_traffic_filtering'] = null;
        $this->rs['falcon_macos_compatible'] = null;
        $this->rs['falcon_installation_paths_valid'] = null;
        $this->rs['falcon_global_status'] = null;

        // Duplicates removed - fields already defined above

        // All columns exist on remote server - no need for schema checking

        if ($serial) {
            $this->retrieve_record($serial);
        }

        $this->serial_number = $serial;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get sensor version stats
	 *
	 * @return array
	 **/
    public function get_crowdstrike_installguard_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN sensor_installguard = 1 AND installed = 1 THEN 1 END) as enabled, 
                    COUNT(CASE WHEN sensor_installguard = 0 AND installed = 1 THEN 1 END) as 'disabled',
                    COUNT(CASE WHEN (sensor_installguard IS NULL OR sensor_installguard = 2) AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    public function get_crowdstrike_sensor_active_stats()
    {
        // Match Uninstall Protection widget semantics: count only explicit 1/0 values
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN sensor_active = 1 AND installed = 1 THEN 1 END) as enabled,
                    COUNT(CASE WHEN sensor_active = 0 AND installed = 1 THEN 1 END) as 'disabled',
                    COUNT(CASE WHEN (sensor_active IS NULL OR sensor_active = 2) AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    /**
     * Get enhanced security compliance statistics
     */
    public function get_crowdstrike_security_compliance_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_full_disk_access = 1 THEN 1 END) as fda_enabled,
                    COUNT(CASE WHEN falcon_full_disk_access = 0 THEN 1 END) as fda_disabled,
                    COUNT(CASE WHEN falcon_rfm_status = 1 THEN 1 END) as rfm_active,
                    COUNT(CASE WHEN falcon_rfm_status = 0 THEN 1 END) as rfm_inactive,
                    COUNT(CASE WHEN falcon_global_status = 1 THEN 1 END) as fully_compliant,
                    COUNT(CASE WHEN falcon_global_status = 0 THEN 1 END) as non_compliant
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                WHERE installed = 1
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    /**
     * Get system extension status statistics
     */
    public function get_crowdstrike_system_extension_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_system_extension_approved = 1 THEN 1 END) as approved,
                    COUNT(CASE WHEN falcon_system_extension_approved = 0 THEN 1 END) as not_approved,
                    COUNT(CASE WHEN falcon_network_filter_active = 1 THEN 1 END) as network_filter_active,
                    COUNT(CASE WHEN falcon_web_filter_active = 1 THEN 1 END) as web_filter_active
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                WHERE installed = 1
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    // Button count methods for widgets - follow pattern of working widgets (no WHERE clause)
    
    public function get_crowdstrike_global_status_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_global_status = 1 AND installed = 1 THEN 1 END) as ok,
                    COUNT(CASE WHEN falcon_global_status = 0 AND installed = 1 THEN 1 END) as issues,
                    COUNT(CASE WHEN (falcon_global_status IS NULL OR falcon_global_status = 2) AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    public function get_crowdstrike_full_disk_access_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_full_disk_access = 1 AND installed = 1 THEN 1 END) as granted,
                    COUNT(CASE WHEN falcon_full_disk_access = 0 AND installed = 1 THEN 1 END) as denied,
                    COUNT(CASE WHEN (falcon_full_disk_access IS NULL OR falcon_full_disk_access = 2) AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    public function get_crowdstrike_rfm_status_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_rfm_status = 1 AND installed = 1 THEN 1 END) as active,
                    COUNT(CASE WHEN falcon_rfm_status = 0 AND installed = 1 THEN 1 END) as inactive,
                    COUNT(CASE WHEN (falcon_rfm_status IS NULL OR falcon_rfm_status = 2) AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    public function get_crowdstrike_system_extension_approved_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_system_extension_approved = 1 AND installed = 1 THEN 1 END) as approved,
                    COUNT(CASE WHEN falcon_system_extension_approved = 0 AND installed = 1 THEN 1 END) as not_approved,
                    COUNT(CASE WHEN falcon_system_extension_approved IS NULL AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    public function get_crowdstrike_network_filter_active_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_network_filter_active = 1 AND installed = 1 THEN 1 END) as active,
                    COUNT(CASE WHEN falcon_network_filter_active = 0 AND installed = 1 THEN 1 END) as inactive,
                    COUNT(CASE WHEN falcon_network_filter_active IS NULL AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    public function get_crowdstrike_web_filter_active_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_web_filter_active = 1 AND installed = 1 THEN 1 END) as active,
                    COUNT(CASE WHEN falcon_web_filter_active = 0 AND installed = 1 THEN 1 END) as inactive,
                    COUNT(CASE WHEN falcon_web_filter_active IS NULL AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    public function get_crowdstrike_service_management_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_service_management_configured = 1 AND installed = 1 THEN 1 END) as configured,
                    COUNT(CASE WHEN falcon_service_management_configured = 0 AND installed = 1 THEN 1 END) as not_configured,
                    COUNT(CASE WHEN falcon_service_management_configured IS NULL AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    public function get_crowdstrike_team_id_valid_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_team_id_valid = 1 AND installed = 1 THEN 1 END) as valid,
                    COUNT(CASE WHEN falcon_team_id_valid = 0 AND installed = 1 THEN 1 END) as invalid,
                    COUNT(CASE WHEN falcon_team_id_valid IS NULL AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    public function get_crowdstrike_cloud_connected_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_cloud_connected = 1 AND installed = 1 THEN 1 END) as connected,
                    COUNT(CASE WHEN falcon_cloud_connected = 0 AND installed = 1 THEN 1 END) as disconnected,
                    COUNT(CASE WHEN falcon_cloud_connected IS NULL AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }

    public function get_crowdstrike_heartbeat_active_stats()
    {
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    COUNT(CASE WHEN falcon_heartbeat_active = 1 AND installed = 1 THEN 1 END) as active,
                    COUNT(CASE WHEN falcon_heartbeat_active = 0 AND installed = 1 THEN 1 END) as inactive,
                    COUNT(CASE WHEN falcon_heartbeat_active IS NULL AND installed = 1 THEN 1 END) as unknown
                FROM crowdstrike
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter();
        return current($this->query($sql));
    }
}
