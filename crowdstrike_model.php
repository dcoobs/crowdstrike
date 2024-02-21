<?php

use CFPropertyList\CFPropertyList;

class Crowdstrike_model extends \Model {

    public function __construct($serial='')
    {
        parent::__construct('id', 'crowdstrike'); // Primary key, tablename
        $this->rs['id'] = '';
        $this->rs['serial_number'] = $serial;
        $this->rs['sensor_id'] = '';
        $this->rs['sensor_version'] = '';
        $this->rs['customer_id'] = '';
        $this->rs['sensor_installguard'] = 0; // Boolean
        $this->rs['sensor_operational'] = 0; // Boolean

        if ($serial) {
            $this->retrieve_record($serial);
        }

        $this->serial_number = $serial;
    }

    // ------------------------------------------------------------------------

    /**
     * Process data sent by postflight
     *
     * @param string data
     *
     **/
     public function process($data)
     {
         $parser = new CFPropertyList();
         $parser->parse($data);
         $plist = $parser->toArray();

         // Process plist only if 'agent_info' key exists in it
         if (array_key_exists('agent_info', $plist)){
            $agent_info = $plist['agent_info'];

            // Process all of the keys in the plist
            foreach (array('agentID', 'version', 'customerID', 'sensor_installguard', 'sensor_operational') as $item) {

                // If key does not exist in $plist, null it
                if ( ! array_key_exists($item, $agent_info) || $agent_info[$item] == '') {
                    $this->$item = null;

                // Set the db fields to be the same as those in the preference file
                } else if ($item == "agentID") {
                    // agentID -> sensor_id
                    $this->sensor_id = str_replace("-", "", strtolower($agent_info[$item])); // Do some formatting so that output matchines CS console
                } else if ($item == "customerID") {
                    // customerID - > customer_id
                    $this->customer_id = str_replace("-", "", $agent_info[$item]); // do some formatting so that output matchines CS console
                } else if ($item == "version") {
                    // version -> sensor_version
                    $this->sensor_version = $agent_info[$item];
                // Process booleans
                } else if (($item == "sensor_installguard" || $item == "sensor_operational") && $agent_info[$item] == "true") {
                    $this->$item = "1"; // Set boolean to true
                } else if (($item == "sensor_installguard" || $item == "sensor_operational") && $agent_info[$item] == "false") {
                    $this->$item = "0"; // Set boolean to true
                }
            }

            // Save the data
            $this->save();
         }
     }
}
