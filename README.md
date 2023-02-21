# CrowdStrike Falcon Module for Munkireport

This module gives info on the CrowdStrike Falcon sensor for a given machine. The data comes from the `/Applications/Falcon.app/Contents/Resources/falconctl stats` command.

Version 3.0 is required for CrowdStrike sensor versions 6.11 and greater. Use version 2.3 for older sensor versions.

![CrowdStrike Listing](https://raw.githubusercontent.com/dcoobs/crowdstrike/master/images/CrowdStrikeListing.png)  
Included are widgets for sensor versions:  
![Sensor Versions Widget](https://raw.githubusercontent.com/dcoobs/crowdstrike/master/images/sensorversion_widget.PNG)  
and for uninstall protection:  
![Uninstall Protection Widget](https://raw.githubusercontent.com/dcoobs/crowdstrike/master/images/uninstallprotection_widget.PNG)

Table Schema
------
* sensor_id - VARCHAR(255) - Unique host ID of the client's sensor
* sensor_version - VARCHAR(255) - Version of the client's sensor
* customer_id - VARCHAR(255) - The customer ID checksum (CCID) for the sensor's instance
* sensor_active - VARCHAR(255) - Sensor operational
* sensor_installguard - INT(11) - Boolean value for the sensor's uninstall protection policy. 1=Enabled 0=Disabled
