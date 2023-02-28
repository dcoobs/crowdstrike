<?php

$this->view('listings/default',
[
  "i18n_title" => 'crowdstrike.listing.title',
  "not_null_column" => 'crowdstrike.sensor_version',
  "js_link" => "module/crowdstrike/js/crowdstrike",
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
      "column" => "reportdata.long_username",
      "i18n_header" => "username",
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
      "column" => "crowdstrike.sensor_active",
      "i18n_header" => "crowdstrike.sensor_active",
      "formatter" => "binaryEnabledDisabled",
      "filter" => "sensor_state",
    ],
    [
      "column" => "crowdstrike.sensor_installguard",
      "i18n_header" => "crowdstrike.sensor_installguard",
      "formatter" => "binaryEnabledDisabled",
      "filter" => "protect_state",
    ],
    [
      "column" => "machine.os_version",
      "i18n_header" => "crowdstrike.os_version",
      "formatter" => "osVersion",
    ],
    [
      "column" => "reportdata.timestamp",
      "i18n_header" => "listing.checkin",
      "formatter" => "timestampToMoment",
    ],
    ]
]);

