<?php

return array(
    'client_tabs' => array(
        'crowdstrike-tab' => array('view' => 'crowdstrike_client_tab', 'i18n' => 'crowdstrike.crowdstrike'),
    ),
    'listings' => array(
        'crowdstrike' => array('view' => 'crowdstrike_listing', 'i18n' => 'crowdstrike.crowdstrike'),
    ),
    'widgets' => array(
        'crowdstrike_installguard' => array('view' => 'crowdstrike_installguard_widget', 'i18n' => 'crowdstrike.installguard-widget'),
        'crowdstrike_sensor_operational' => array('view' => 'crowdstrike_sensor_operational_widget', 'i18n' => 'crowdstrike.sensor-operational-widget'),
        'crowdstrike_sensor_versions' => array('view' => 'crowdstrike_sensor_versions_widget', 'i18n' => 'sensor-versions-widget'),
       'crowdstrike_sensor_versions_graph' => array('view' => 'crowdstrike_sensor_versions_graph_widget', 'i18n' => 'sensor-versions-widget'),
    ),
    'reports' => array(
        'crowdstrike' => array('view' => 'crowdstrike_report', 'i18n' => 'crowdstrike.report'),
    ),
);
