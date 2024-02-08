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
     * Get data for scroll widget
     *
     * @return void
     * @author tuxudo
     **/
    public function get_scroll_widget($column)
    {
        // Remove non-column name characters
        $column = preg_replace("/[^A-Za-z0-9_\-]]/", '', $column);

        $sql = "SELECT COUNT(CASE WHEN ".$column." <> '' AND ".$column." IS NOT NULL THEN 1 END) AS count, ".$column." 
                    FROM crowdstrike
                    LEFT JOIN reportdata USING (serial_number)
                    ".get_machine_group_filter()."
                    AND ".$column." <> '' AND ".$column." IS NOT NULL 
                    GROUP BY ".$column."
                    ORDER BY count DESC";

        $queryobj = new Crowdstrike_model;
        jsonView($queryobj->query($sql));
    }

    /**
     * Get data for button widget
     *
     * @return void
     * @author tuxudo
     **/
    public function get_button_widget($column)
    {
        // Remove non-column name characters
        $column = preg_replace("/[^A-Za-z0-9_\-]]/", '', $column);

        $sql = "SELECT COUNT(1) as total,
                    COUNT(CASE WHEN ".$column." = 1 THEN 1 END) AS 'yes',
                    COUNT(CASE WHEN ".$column." = 0 THEN 1 END) AS 'no'
                    from crowdstrike
                    LEFT JOIN reportdata USING (serial_number)
                    WHERE ".get_machine_group_filter('');

        $out = [];
        $queryobj = new Crowdstrike_model();
        foreach($queryobj->query($sql)[0] as $label => $value){
                $out[] = ['label' => $label, 'count' => $value];
        }

        jsonView($out);
    }

    // /**
    //  * Get sensor version stats
    //  *
    //  * @return void
    //  * @author dcoobs
    //  **/

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
} //end of class
