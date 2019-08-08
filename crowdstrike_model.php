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

             $this->sensor_id = $plist['sensor_id'];
             $this->sensor_version = $plist['sensor_version'];
             $this->customer_id = $plist['customer_id'];
             $this->sensor_installguard = $plist['sensor_installguard'];
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
}
