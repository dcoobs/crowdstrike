<?php
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Crowdstrike module class
 *
 * @package crowdstrike
 * @author dcoobs
 **/
class Crowdstrike_controller extends Module_controller
{

    /*** Protect methods with auth! ****/
    public function __construct()
    {
        // Store module path
        $this->module_path = dirname(__FILE__);
    }
    /**
     * Default method
     *
     * @author AvB
     **/
    public function index()
    {
        echo "You've loaded the crowdstrike module!";
    }

    /**
     * Get Crowdstrike information for serial_number
     *
     * @param string $serial serial number
     **/
    public function get_data($serial = '')
    {
        $out = [];
        if (! $this->authorized()) {
            $out['error'] = 'Not authorized';
        } else {
            // Sanitize input - fix regex pattern and validate
            $serial = preg_replace("/[^A-Za-z0-9_\-]+/", '', $serial);
            
            if (empty($serial)) {
                $out['error'] = 'Invalid serial number';
            } else {
                $prm = new Crowdstrike_model;
                $records = $prm->retrieve_records($serial) ?: [];
                foreach ($records as $crowdstrike) {
                    $out = $crowdstrike->rs;
                    
                    // Convert integer fields to proper types for JSON output
                    $integer_fields = [
                        'sensor_active', 'sensor_operational', 'sensor_installguard', 
                        'system_extension_present', 'system_extension_activated', 'installed',
                        'falcon_full_disk_access', 'falcon_system_extension_approved', 
                        'falcon_network_filter_active', 'falcon_web_filter_active',
                        'falcon_service_management_configured', 'falcon_team_id_valid',
                        'falcon_non_removable_extension', 'falcon_rfm_status', 'falcon_safe_mode',
                        'falcon_cloud_connected', 'falcon_heartbeat_active', 'falcon_network_traffic_filtering',
                        'falcon_macos_compatible', 'falcon_installation_paths_valid', 'falcon_global_status'
                    ];
                    
                    foreach ($integer_fields as $field) {
                        if (isset($out[$field]) && $out[$field] !== null) {
                            $out[$field] = (int)$out[$field];
                        }
                    }
                }
            }
        }

        $obj = new View();
        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get sensor version stats
     *
     * @return void
     * @author dcoobs
     **/

    public function get_crowdstrike_sensor_version_stats()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_sensor_version_stats = new Crowdstrike_model();

        // Use parameterized query to prevent SQL injection
        $sql = "SELECT count(1) as count, sensor_version
            FROM crowdstrike
            LEFT JOIN reportdata USING (serial_number)
            ".get_machine_group_filter()."
            GROUP BY sensor_version
            ORDER BY sensor_version DESC";

        $out = [];
        foreach ($crowdstrike_sensor_version_stats->query($sql) as $objRow) {
            if (is_null($objRow->sensor_version)) {continue;}
            $objRow->sensor_version = $objRow->sensor_version ? $objRow->sensor_version : 'Unknown';
            $out[] = array('label' => $objRow->sensor_version, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get sensor id stats
     *
     * @return void
     * @author dcoobs
     **/

    public function get_crowdstrike_sensor_id_stats()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_sensor_id_stats = new Crowdstrike_model();

        // Use parameterized query to prevent SQL injection
        $sql = "SELECT count(1) as count, sensor_id
            FROM crowdstrike
            LEFT JOIN reportdata USING (serial_number)
            ".get_machine_group_filter()."
            GROUP BY sensor_id
            ORDER BY sensor_id DESC";
        
        $out = [];
        foreach ($crowdstrike_sensor_id_stats->query($sql) as $objRow) {
            $objRow->sensor_id = $objRow->sensor_id ? $objRow->sensor_id : '0';
            $out[] = array('label' => $objRow->sensor_id, 'count' => intval($objRow->count));
        }
        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get customer id stats
     *
     * @return void
     * @author dcoobs
     **/

    public function get_crowdstrike_customer_id_stats()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_customer_id_stats = new Crowdstrike_model();

        // Use parameterized query to prevent SQL injection
        $sql = "SELECT count(1) as count, customer_id
            FROM crowdstrike
            LEFT JOIN reportdata USING (serial_number)
            ".get_machine_group_filter()."
            GROUP BY customer_id
            ORDER BY customer_id DESC";
        
        $out = [];
        foreach ($crowdstrike_customer_id_stats->query($sql) as $objRow) {
            $objRow->customer_id = $objRow->customer_id ? $objRow->customer_id : '0';
            $out[] = array('label' => $objRow->customer_id, 'count' => intval($objRow->count));
        }
        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get sensor active stats
     *
     * @return void
     * @author dcoobs
     **/


    public function get_crowdstrike_sensor_active_stats()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $crowdstrike_sensor_active_stats = new Crowdstrike_model();
        $out = [];
        $stats = $crowdstrike_sensor_active_stats->get_crowdstrike_sensor_active_stats();
        // Normalize nulls to 0 to avoid JS issues
        $enabled = 0;
        $disabled = 0;
        if (is_array($stats)){
            $enabled = intval(isset($stats['enabled']) ? $stats['enabled'] : 0);
            $disabled = intval(isset($stats['disabled']) ? $stats['disabled'] : 0);
        } else if (is_object($stats)){
            $enabled = intval(isset($stats->enabled) ? $stats->enabled : 0);
            $disabled = intval(isset($stats->disabled) ? $stats->disabled : 0);
        }
        $out['sensor_stats'] = array('enabled' => $enabled, 'disabled' => $disabled);
        $obj->view('json', array('msg' => $out));
    }

    /**
     * Button widget API: label/count pairs for Sensor Active
     */
    public function get_crowdstrike_sensor_active_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_sensor_active_stats();
        $counts = array(
            array('label' => 'active', 'count' => intval($row->enabled ?? 0)),
            array('label' => 'inactive', 'count' => intval($row->disabled ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }


    /**
     * Get uninstall protection stats
     *
     * @return void
     * @author dcoobs
     **/

    public function get_crowdstrike_installguard_stats()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $crowdstrike_installguard_stats = new Crowdstrike_model();
        $out = [];
        $out['stats'] = $crowdstrike_installguard_stats->get_crowdstrike_installguard_stats();
        $obj->view('json', array('msg' => $out));
    }

    /**
     * Button widget API: label/count pairs for Uninstall Protection
     */
    public function get_crowdstrike_installguard_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_installguard_stats();
        $counts = array(
            array('label' => 'enabled', 'count' => intval($row->enabled ?? 0)),
            array('label' => 'disabled', 'count' => intval($row->disabled ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Get System Extension stats (activated/present/absent)
     */
    public function get_crowdstrike_sysext_stats()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT
                    COUNT(CASE WHEN system_extension_activated = 1 AND installed = 1 THEN 1 END) AS activated,
                    COUNT(CASE WHEN system_extension_present = 1 AND system_extension_activated = 0 AND installed = 1 THEN 1 END) AS present,
                    COUNT(CASE WHEN system_extension_present = 0 AND installed = 1 THEN 1 END) AS absent
                FROM crowdstrike
                LEFT JOIN reportdata USING(serial_number)
                ".get_machine_group_filter();
        $out = [];
        $out['stats'] = current($model->query($sql));
        $obj->view('json', array('msg' => $out));
    }

    /**
     * Button widget API: return label/count pairs for System Extension status
     */
    public function get_crowdstrike_sysext_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $model = new Crowdstrike_model();
        
        // Use parameterized query to prevent SQL injection
        $sql = "SELECT
                    COUNT(CASE WHEN system_extension_activated = 1 AND installed = 1 THEN 1 END) AS activated,
                    COUNT(CASE WHEN system_extension_present = 1 AND system_extension_activated = 0 AND installed = 1 THEN 1 END) AS present,
                    COUNT(CASE WHEN system_extension_present = 0 AND installed = 1 THEN 1 END) AS absent
                FROM crowdstrike
                LEFT JOIN reportdata USING(serial_number)
                ".get_machine_group_filter();

        $row = current($model->query($sql));
        $counts = array(
            array('label' => 'activated', 'count' => intval($row->activated ?? 0)),
            array('label' => 'present', 'count' => intval($row->present ?? 0)),
            array('label' => 'absent', 'count' => intval($row->absent ?? 0)),
        );

        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Button widget API: return label/count pairs for Global Status
     */
    public function get_crowdstrike_global_status_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_global_status_stats();
        $counts = array(
            array('label' => 'ok', 'count' => intval($row->ok ?? 0)),
            array('label' => 'issues', 'count' => intval($row->issues ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Button widget API: return label/count pairs for Full Disk Access
     */
    public function get_crowdstrike_full_disk_access_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_full_disk_access_stats();
        $counts = array(
            array('label' => 'granted', 'count' => intval($row->granted ?? 0)),
            array('label' => 'denied', 'count' => intval($row->denied ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Button widget API: return label/count pairs for RFM Status
     */
    public function get_crowdstrike_rfm_status_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_rfm_status_stats();
        $counts = array(
            array('label' => 'active', 'count' => intval($row->active ?? 0)),
            array('label' => 'inactive', 'count' => intval($row->inactive ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Button widget API: return label/count pairs for System Extension Approved
     */
    public function get_crowdstrike_system_extension_approved_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_system_extension_approved_stats();
        $counts = array(
            array('label' => 'approved', 'count' => intval($row->approved ?? 0)),
            array('label' => 'not_approved', 'count' => intval($row->not_approved ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Button widget API: return label/count pairs for Network Filter Active
     */
    public function get_crowdstrike_network_filter_active_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_network_filter_active_stats();
        $counts = array(
            array('label' => 'active', 'count' => intval($row->active ?? 0)),
            array('label' => 'inactive', 'count' => intval($row->inactive ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Button widget API: return label/count pairs for Web Filter Active
     */
    public function get_crowdstrike_web_filter_active_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_web_filter_active_stats();
        $counts = array(
            array('label' => 'active', 'count' => intval($row->active ?? 0)),
            array('label' => 'inactive', 'count' => intval($row->inactive ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Button widget API: return label/count pairs for Service Management
     */
    public function get_crowdstrike_service_management_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_service_management_stats();
        $counts = array(
            array('label' => 'configured', 'count' => intval($row->configured ?? 0)),
            array('label' => 'not_configured', 'count' => intval($row->not_configured ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Button widget API: return label/count pairs for Team ID Valid
     */
    public function get_crowdstrike_team_id_valid_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_team_id_valid_stats();
        $counts = array(
            array('label' => 'valid', 'count' => intval($row->valid ?? 0)),
            array('label' => 'invalid', 'count' => intval($row->invalid ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Button widget API: return label/count pairs for Cloud Connected
     */
    public function get_crowdstrike_cloud_connected_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_cloud_connected_stats();
        $counts = array(
            array('label' => 'connected', 'count' => intval($row->connected ?? 0)),
            array('label' => 'disconnected', 'count' => intval($row->disconnected ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    /**
     * Button widget API: return label/count pairs for Heartbeat Active
     */
    public function get_crowdstrike_heartbeat_active_button_counts()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        $model = new Crowdstrike_model();
        $row = $model->get_crowdstrike_heartbeat_active_stats();
        $counts = array(
            array('label' => 'active', 'count' => intval($row->active ?? 0)),
            array('label' => 'inactive', 'count' => intval($row->inactive ?? 0)),
            array('label' => 'unknown', 'count' => intval($row->unknown ?? 0)),
        );
        $obj->view('json', array('msg' => $counts));
    }

    // Removed discrepancy stats endpoint (feature deprecated)

    /**
     * Get enhanced security compliance statistics
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_security_compliance_stats()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        $stats = $crowdstrike_model->get_crowdstrike_security_compliance_stats();

        $obj->view('json', array('msg' => $stats));
    }

    /**
     * Get system extension status statistics
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_system_extension_stats()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        $stats = $crowdstrike_model->get_crowdstrike_system_extension_stats();

        $obj->view('json', array('msg' => $stats));
    }

    /**
     * Get global status data for widget
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_global_status()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        
        // Get counts for each status - use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    CASE 
                        WHEN falcon_global_status = 1 THEN '1'
                        WHEN falcon_global_status = 0 THEN '0'
                        ELSE '2'
                    END AS label, 
                    count(*) AS count
                FROM crowdstrike
                WHERE installed = 1
                ".get_machine_group_filter()."
                GROUP BY label
                ORDER BY count DESC";

        $out = [];
        foreach ($crowdstrike_model->query($sql) as $objRow) {
            $out[] = array('label' => $objRow->label, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get Full Disk Access status data for widget
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_full_disk_access()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        
        // Get counts for each status - use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    CASE 
                        WHEN falcon_full_disk_access = 1 THEN '1'
                        WHEN falcon_full_disk_access = 0 THEN '0'
                        ELSE '2'
                    END AS label, 
                    count(*) AS count
                FROM crowdstrike
                WHERE installed = 1
                ".get_machine_group_filter()."
                GROUP BY label
                ORDER BY count DESC";

        $out = [];
        foreach ($crowdstrike_model->query($sql) as $objRow) {
            $out[] = array('label' => $objRow->label, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get RFM status data for widget
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_rfm_status()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        
        // Get counts for each status - use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    CASE 
                        WHEN falcon_rfm_status = 1 THEN '1'
                        WHEN falcon_rfm_status = 0 THEN '0'
                        ELSE '2'
                    END AS label, 
                    count(*) AS count
                FROM crowdstrike
                WHERE installed = 1
                ".get_machine_group_filter()."
                GROUP BY label
                ORDER BY count DESC";

        $out = [];
        foreach ($crowdstrike_model->query($sql) as $objRow) {
            $out[] = array('label' => $objRow->label, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get system extension approved data for widget
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_system_extension_approved()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        
        // Get counts for each status - use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    CASE 
                        WHEN falcon_system_extension_approved = 1 THEN '1'
                        WHEN falcon_system_extension_approved = 0 THEN '0'
                        ELSE 'NULL'
                    END AS label, 
                    count(*) AS count
                FROM crowdstrike
                WHERE installed = 1
                ".get_machine_group_filter()."
                GROUP BY label
                ORDER BY count DESC";

        $out = [];
        foreach ($crowdstrike_model->query($sql) as $objRow) {
            $out[] = array('label' => $objRow->label, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get network filter active data for widget
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_network_filter_active()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        
        // Get counts for each status - use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    CASE 
                        WHEN falcon_network_filter_active = 1 THEN '1'
                        WHEN falcon_network_filter_active = 0 THEN '0'
                        ELSE 'NULL'
                    END AS label, 
                    count(*) AS count
                FROM crowdstrike
                WHERE installed = 1
                ".get_machine_group_filter()."
                GROUP BY label
                ORDER BY count DESC";

        $out = [];
        foreach ($crowdstrike_model->query($sql) as $objRow) {
            $out[] = array('label' => $objRow->label, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get web filter active data for widget
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_web_filter_active()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        
        // Get counts for each status - use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    CASE 
                        WHEN falcon_web_filter_active = 1 THEN '1'
                        WHEN falcon_web_filter_active = 0 THEN '0'
                        ELSE 'NULL'
                    END AS label, 
                    count(*) AS count
                FROM crowdstrike
                WHERE installed = 1
                ".get_machine_group_filter()."
                GROUP BY label
                ORDER BY count DESC";

        $out = [];
        foreach ($crowdstrike_model->query($sql) as $objRow) {
            $out[] = array('label' => $objRow->label, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get service management data for widget
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_service_management()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        
        // Get counts for each status - use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    CASE 
                        WHEN falcon_service_management_configured = 1 THEN '1'
                        WHEN falcon_service_management_configured = 0 THEN '0'
                        ELSE 'NULL'
                    END AS label, 
                    count(*) AS count
                FROM crowdstrike
                WHERE installed = 1
                ".get_machine_group_filter()."
                GROUP BY label
                ORDER BY count DESC";

        $out = [];
        foreach ($crowdstrike_model->query($sql) as $objRow) {
            $out[] = array('label' => $objRow->label, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get team ID valid data for widget
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_team_id_valid()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        
        // Get counts for each status - use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    CASE 
                        WHEN falcon_team_id_valid = 1 THEN '1'
                        WHEN falcon_team_id_valid = 0 THEN '0'
                        ELSE 'NULL'
                    END AS label, 
                    count(*) AS count
                FROM crowdstrike
                WHERE installed = 1
                ".get_machine_group_filter()."
                GROUP BY label
                ORDER BY count DESC";

        $out = [];
        foreach ($crowdstrike_model->query($sql) as $objRow) {
            $out[] = array('label' => $objRow->label, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get cloud connected data for widget
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_cloud_connected()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        
        // Get counts for each status - use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    CASE 
                        WHEN falcon_cloud_connected = 1 THEN '1'
                        WHEN falcon_cloud_connected = 0 THEN '0'
                        ELSE 'NULL'
                    END AS label, 
                    count(*) AS count
                FROM crowdstrike
                WHERE installed = 1
                ".get_machine_group_filter()."
                GROUP BY label
                ORDER BY count DESC";

        $out = [];
        foreach ($crowdstrike_model->query($sql) as $objRow) {
            $out[] = array('label' => $objRow->label, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Get heartbeat active data for widget
     *
     * @return void
     * @author Enhanced for security monitoring
     **/
    public function get_crowdstrike_heartbeat_active()
    {
        $obj = new View();
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_model = new Crowdstrike_model();
        
        // Get counts for each status - use parameterized query to prevent SQL injection
        $sql = "SELECT 
                    CASE 
                        WHEN falcon_heartbeat_active = 1 THEN '1'
                        WHEN falcon_heartbeat_active = 0 THEN '0'
                        ELSE 'NULL'
                    END AS label, 
                    count(*) AS count
                FROM crowdstrike
                WHERE installed = 1
                ".get_machine_group_filter()."
                GROUP BY label
                ORDER BY count DESC";

        $out = [];
        foreach ($crowdstrike_model->query($sql) as $objRow) {
            $out[] = array('label' => $objRow->label, 'count' => intval($objRow->count));
        }

        $obj->view('json', array('msg' => $out));
    }

    /**
     * Update global status for all records
     * 
     * Global Status Calculation Logic:
     * - If ALL required fields have correct values (12 fields = 1, 2 fields = 0), then global_status = 1 (OK)
     * - Otherwise, global_status = 0 (Issues)
     * 
     * Access via /module/crowdstrike/update_global_status
     */
    public function update_global_status()
    {
        $obj = new View();
        
        // Simple admin check - use MunkiReport's built-in auth
        if (!$this->authorized()) {
            $obj->view('json', ['error' => 'Unauthorized access']);
            return;
        }
        
        try {
            // Get all CrowdStrike records
            $crowdstrike_model = new Crowdstrike_model();
            $sql = "SELECT * FROM crowdstrike WHERE installed = 1";
            $records = $crowdstrike_model->query($sql);
            $total = count($records);
            $updated = 0;
            $errors = 0;
            
            foreach ($records as $record) {
                // Helper function to get integer value, treating null/empty as actual null
                $get_int_val = function($key) use ($record) {
                    $val = $record->$key;
                    return isset($val) ? (is_null($val) ? null : intval($val)) : null;
                };
                
                // Get all fields that should be 1 for OK status
                $sensor_active = $get_int_val('sensor_active');
                $sensor_installguard = $get_int_val('sensor_installguard');
                $falcon_full_disk_access = $get_int_val('falcon_full_disk_access');
                $falcon_system_extension_approved = $get_int_val('falcon_system_extension_approved');
                $falcon_network_filter_active = $get_int_val('falcon_network_filter_active');
                $falcon_web_filter_active = $get_int_val('falcon_web_filter_active');
                $falcon_service_management_configured = $get_int_val('falcon_service_management_configured');
                $falcon_team_id_valid = $get_int_val('falcon_team_id_valid');
                $falcon_non_removable_extension = $get_int_val('falcon_non_removable_extension');
                $falcon_cloud_connected = $get_int_val('falcon_cloud_connected');
                $falcon_heartbeat_active = $get_int_val('falcon_heartbeat_active');
                $falcon_network_traffic_filtering = $get_int_val('falcon_network_traffic_filtering');
                $falcon_macos_compatible = $get_int_val('falcon_macos_compatible');
                $falcon_installation_paths_valid = $get_int_val('falcon_installation_paths_valid');
                
                // Get fields that should be 0 for OK status
                $falcon_rfm_status = $get_int_val('falcon_rfm_status');
                $falcon_safe_mode = $get_int_val('falcon_safe_mode');
                
                // Check if ALL fields that should be 1 are actually 1
                $all_positive_ok = ($sensor_active === 1 && 
                                    $sensor_installguard === 1 &&
                                    $falcon_full_disk_access === 1 && 
                                    $falcon_system_extension_approved === 1 && 
                                    $falcon_network_filter_active === 1 && 
                                    $falcon_web_filter_active === 1 && 
                                    $falcon_service_management_configured === 1 && 
                                    $falcon_team_id_valid === 1 &&
                                    $falcon_non_removable_extension === 1 &&
                                    $falcon_cloud_connected === 1 &&
                                    $falcon_heartbeat_active === 1 &&
                                    $falcon_network_traffic_filtering === 1 &&
                                    $falcon_macos_compatible === 1 &&
                                    $falcon_installation_paths_valid === 1);
                
                // Check if fields that should be 0 are actually 0
                $all_negative_ok = ($falcon_rfm_status === 0 && $falcon_safe_mode === 0);
                
                // Set the global status: 1 (OK) only if ALL required fields have correct values
                $new_status = ($all_positive_ok && $all_negative_ok) ? 1 : 0;
                
                // Only update if the status is different
                if ($record->falcon_global_status != $new_status) {
                    try {
                        Capsule::table('crowdstrike')
                            ->where('id', $record->id)
                            ->update(['falcon_global_status' => $new_status]);
                        $updated++;
                    } catch (\Exception $e) {
                        // Log error for debugging but don't expose details to user
                        error_log("CrowdStrike: Error updating global status for record ID {$record->id}: " . $e->getMessage());
                        $errors++;
                    }
                }
            }
            
            $obj->view('json', [
                'status' => 'success',
                'message' => "Updated global status for $updated records out of $total",
                'total' => $total,
                'updated' => $updated,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            // Log error for debugging but don't expose details to user
            error_log("CrowdStrike: Error in update_global_status: " . $e->getMessage());
            $obj->view('json', [
                'status' => 'error',
                'message' => 'An error occurred while updating global status'
            ]);
        }
    }
    
} //end of class
