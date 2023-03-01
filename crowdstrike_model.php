<?php

use CFPropertyList\CFPropertyList;

class Crowdstrike_model extends \Model {

	public function __construct($serial='')
	{
		parent::__construct('id', 'crowdstrike'); //primary key, tablename
		$this->rs['id'] = '';
		$this->rs['serial_number'] = $serial; $this->rt['serial_number'] = 'VARCHAR(255) UNIQUE';
                $this->rs['sensor_id'] = '';
                $this->rs['sensor_version'] = '';
                $this->rs['customer_id'] = '';
                $this->rs['sensor_installguard'] = 0; //boolean
                $this->rs['sensor_operational'] = 0; //boolean

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

             $this->sensor_id = str_replace("-", "", strtolower($plist['agent_info']['agentID'])); # do some formatting so that output matchines CS console
             $this->sensor_version = $plist['agent_info']['version'];
             $this->customer_id = str_replace("-", "", $plist['agent_info']['customerID']); # do some formatting so that output matchines CS console
             $this->sensor_installguard = $plist['agent_info']['sensor_installguard'];
             $this->sensor_operational = $plist['agent_info']['sensor_operational'];
             $this->save();
         }

         public function get_crowdstrike_installguard_stats()
         {
             $sql = "SELECT COUNT(CASE WHEN sensor_installguard = 1 THEN 1 END) as enabled, COUNT(CASE WHEN sensor_installguard = 0 THEN 1 END) as 'disabled'
                 FROM crowdstrike
                 LEFT JOIN reportdata USING(serial_number)
                 ".get_machine_group_filter();
             return current($this->query($sql));
         }

         public function get_crowdstrike_sensor_operational_stats()
         {
             $sql = "SELECT COUNT(CASE WHEN sensor_operational = 1 THEN 1 END) as enabled, COUNT(CASE WHEN sensor_operational = 0 THEN 1 END) as 'disabled'
                 FROM crowdstrike
                 LEFT JOIN reportdata USING(serial_number)
                 ".get_machine_group_filter();
             return current($this->query($sql));
         }

}
