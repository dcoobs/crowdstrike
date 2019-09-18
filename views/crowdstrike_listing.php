<?php

$this->view('listings/default',
[
  "i18n_title" => 'crowdstrike.listing.title',
  "table" => [
    [
      "column" => "machine.computer_name",
      "i18n_header" => "listing.computername",
      "formatter" => "clientDetail",
      "tab_link" => "crowdstrike-tab",
    ],
    [
      "column" => "reportdata.serial_number",
      "i18n_header" => "serial",
    ],
    [
      "column" => "crowdstrike.sensor_id",
      "i18n_header" => "crowdstrike.sensor_id",
    ],
    [
      "column" => "crowdstrike.sensor_version",
      "i18n_header" => "crowdstrike.sensor_version",
    ],
    [
      "column" => "crowdstrike.customer_id",
      "i18n_header" => "crowdstrike.customer_id",
    ],
    [
      "column" => "crowdstrike.sensor_installguard",
      "i18n_header" => "crowdstrike.sensor_installguard",
      "formatter" => "binaryEnabledDisabled",
    ],
  ]
]);
