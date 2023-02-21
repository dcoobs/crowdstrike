<?php
/**
 * Crowdstrike module class
 *
 * @package munkireport
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
        echo "You've loaded the Crowdstrike module!";
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
            $prm = new Crowdstrike_model;
            foreach ($prm->retrieve_records($serial) as $crowdstrike) {
                $out = $crowdstrike->rs;
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
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_sensor_version_stats = new Crowdstrike_model();

        $sql = "SELECT count(1) as count, sensor_version
            FROM crowdstrike
            LEFT JOIN reportdata USING (serial_number)
            ".get_machine_group_filter()."
            GROUP BY sensor_version
            ORDER BY sensor_version DESC";

        $out = [];
        foreach ($crowdstrike_sensor_version_stats->query($sql) as $obj) {
            if (is_null($obj->sensor_version)) {continue;}
            $obj->sensor_version = $obj->sensor_version ? $obj->sensor_version : 'Unknown';
            $out[] = array('label' => $obj->sensor_version, 'count' => intval($obj->count));
        }

        $obj = new View();
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
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_sensor_id_stats = new Crowdstrike_model();

        $sql = "SELECT count(1) as count, sensor_id
            FROM crowdstrike
            LEFT JOIN reportdata USING (serial_number)
            ".get_machine_group_filter()."
            GROUP BY sensor_id
            ORDER BY sensor_id DESC";
        
        $out = [];
        foreach ($crowdstrike_sensor_id_stats->query($sql) as $obj) {
            $obj->sensor_id = $obj->sensor_id ? $obj->sensor_id : '0';
            $out[] = array('label' => $obj->sensor_id, 'count' => intval($obj->count));
        }
        $obj = new View();
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
        if(! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }

        $crowdstrike_customer_id_stats = new Crowdstrike_model();

        $sql = "SELECT count(1) as count, customer_id
            FROM crowdstrike
            LEFT JOIN reportdata USING (serial_number)
            ".get_machine_group_filter()."
            GROUP BY customer_id
            ORDER BY customer_id DESC";
        
        $out = [];
        foreach ($crowdstrike_customer_id_stats->query($sql) as $obj) {
            $obj->customer_id = $obj->customer_id ? $obj->customer_id : '0';
            $out[] = array('label' => $obj->customer_id, 'count' => intval($obj->count));
        }
        $obj = new View();
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
        $out['sensor_stats'] = $crowdstrike_sensor_active_stats->get_crowdstrike_sensor_active_stats();
        $obj->view('json', array('msg' => $out));
    }


    /**
     * Get installguard/uninstall protection stats
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

    
} //end of class
