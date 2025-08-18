// CrowdStrike JavaScript module loaded

// IMPORTANT: Most filters now include "installed = 1" requirement to match widget behavior
// This ensures that clicking widget buttons returns the same count as shown in the widget
// The installed column is at index 18 in the listing table

// Core filter functions for 1/0/NULL values
var filter_yes = function(colNumber, d) {
    d.columns[colNumber].search.value = '= 1';
    d.search.value = '';
}

var filter_no = function(colNumber, d) {
    d.columns[colNumber].search.value = '= 0';
    d.search.value = '';
}

var filter_unknown = function(colNumber, d) {
    d.columns[colNumber].search.value = 'IS NULL';
    d.search.value = '';
}

// Filters

var sensor_state = function(colNumber, d){
    // Look for 'inactive' keyword
    if(d.search.value.match(/^inactive$/))
    {
        // Add column specific search
        d.columns[colNumber].search.value = '!= 1';
        // Also filter for installed machines (column 18: crowdstrike.installed)
        d.columns[18].search.value = '= 1';
        // Clear global search
        d.search.value = '';
    }

    // Look for 'active' keyword
    if(d.search.value.match(/^active$/))
    {
        // Add column specific search
        d.columns[colNumber].search.value = '= 1';
        // Also filter for installed machines (column 18: crowdstrike.installed)
        d.columns[18].search.value = '= 1';
        // Clear global search
        d.search.value = '';
    }

    // Look for 'sensor_active_unknown' keyword
    if(d.search.value.match(/^sensor_active_unknown$/))
    {
        // Add column specific search
        d.columns[colNumber].search.value = 'IS NULL';
        // Also filter for installed machines (column 18: crowdstrike.installed)
        d.columns[18].search.value = '= 1';
        // Clear global search
        d.search.value = '';
    }
}

var protect_state = function(colNumber, d){
    // Look for 'notprotected' keyword
    if(d.search.value.match(/^notprotected$/))
    {
        // Add column specific search
        d.columns[colNumber].search.value = '= 0';
        // Also filter for installed machines (column 18: crowdstrike.installed)
        d.columns[18].search.value = '= 1';
        // Clear global search
        d.search.value = '';
    }

    // Look for 'protected' keyword
    if(d.search.value.match(/^protected$/))
    {
        // Add column specific search
        d.columns[colNumber].search.value = '= 1';
        // Also filter for installed machines (column 18: crowdstrike.installed)
        d.columns[18].search.value = '= 1';
        // Clear global search
        d.search.value = '';
    }

    // Look for 'uninstall protection unknown' keyword
    if(d.search.value.match(/^installguard_unknown$/))
    {
        // Add column specific search
        d.columns[colNumber].search.value = 'IS NULL';
        // Also filter for installed machines (column 18: crowdstrike.installed)
        d.columns[18].search.value = '= 1';
        // Clear global search
        d.search.value = '';
    }
}

// Global Status Filter
var global_status_filter = function(colNumber, d){
    if(d.search.value.match(/^global_status_ok$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^global_status_issue$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^global_status_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Full Disk Access Filter
var fda_filter = function(colNumber, d){
    if(d.search.value.match(/^full_disk_access_granted_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^full_disk_access_granted_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^full_disk_access_granted_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// System Extension Approved Filter
var sysext_approved_filter = function(colNumber, d){
    if(d.search.value.match(/^system_extension_approved_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^system_extension_approved_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^system_extension_approved_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Network Filter Active Filter
var network_filter_filter = function(colNumber, d){
    if(d.search.value.match(/^network_filter_active_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^network_filter_active_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^network_filter_active_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Web Filter Active Filter
var web_filter_filter = function(colNumber, d){
    if(d.search.value.match(/^web_filter_active_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^web_filter_active_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^web_filter_active_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Team ID Valid Filter
var team_id_filter = function(colNumber, d){
    if(d.search.value.match(/^team_id_valid_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^team_id_valid_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^team_id_valid_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Cloud Connected Filter
var cloud_filter = function(colNumber, d){
    if(d.search.value.match(/^cloud_connected_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^cloud_connected_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^cloud_connected_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Heartbeat Active Filter
var heartbeat_filter = function(colNumber, d){
    if(d.search.value.match(/^heartbeat_active_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^heartbeat_active_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^heartbeat_active_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// RFM Status Filter
var rfm_status_filter = function(colNumber, d){
    if(d.search.value.match(/^rfm_status_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^rfm_status_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^rfm_status_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Safe Mode Filter
var safe_mode_filter = function(colNumber, d){
    if(d.search.value.match(/^safe_mode_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^safe_mode_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^safe_mode_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Installation Paths Valid Filter
var installation_paths_filter = function(colNumber, d){
    if(d.search.value.match(/^installation_paths_valid_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^installation_paths_valid_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^installation_paths_valid_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// macOS Compatible Filter
var macos_compatible_filter = function(colNumber, d){
    if(d.search.value.match(/^macos_compatible_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^macos_compatible_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^macos_compatible_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Network Traffic Filtering Filter
var network_traffic_filter = function(colNumber, d){
    if(d.search.value.match(/^network_traffic_filtering_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^network_traffic_filtering_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^network_traffic_filtering_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Non Removable Extension Filter
var non_removable_filter = function(colNumber, d){
    if(d.search.value.match(/^non_removable_extension_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^non_removable_extension_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^non_removable_extension_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Installed Filter
var installed_filter = function(colNumber, d){
    if(d.search.value.match(/^installed_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^installed_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^installed_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// Service Management Filter
var service_management_filter = function(colNumber, d){
    if(d.search.value.match(/^service_management_yes$/)){
        filter_yes(colNumber, d);
    }
    if(d.search.value.match(/^service_management_no$/)){
        filter_no(colNumber, d);
    }
    if(d.search.value.match(/^service_management_unknown$/)){
        filter_unknown(colNumber, d);
    }
}

// discrepancy filter removed (feature deprecated)

// Custom formatters for special fields

// Format RFM Status: Yes (RFM Mode) = danger, No = success
var formatCrowdstrikeRFMStatus = function(col, row) {
    var cell = $('td:eq('+col+')', row),
        value = cell.text().trim();
    
    switch (value) {
        case '1':
            value = '<span class="label label-danger">Yes (RFM Mode)</span>';
            break;
        case '0':
            value = '<span class="label label-success">No</span>';
            break;
        default:
            value = '<span class="label label-warning">Unknown</span>';
    }
    
    cell.html(value);
}

// Format Safe Mode: Yes = warning, No = success
var formatCrowdstrikeSafeMode = function(col, row) {
    var cell = $('td:eq('+col+')', row),
        value = cell.text().trim();
    
    switch (value) {
        case '1':
            value = '<span class="label label-warning">Yes</span>';
            break;
        case '0':
            value = '<span class="label label-success">No</span>';
            break;
        default:
            value = '<span class="label label-warning">Unknown</span>';
    }
    
    cell.html(value);
}

// Format Global Status: 1 = OK (success), 0 = Issues (danger), NULL = Unknown (warning)
var formatCrowdstrikeGlobalStatus = function(col, row) {
    var cell = $('td:eq('+col+')', row),
        value = cell.text().trim();
    
    switch (value) {
        case '1':
            value = '<span class="label label-success">OK</span>';
            break;
        case '0':
            value = '<span class="label label-danger">Issues</span>';
            break;
        default:
            value = '<span class="label label-warning">Unknown</span>';
    }
    
    cell.html(value);
}

// Format Boolean fields with success/danger classes: 1 = Yes (success), 0 = No (danger), NULL = Unknown (warning)
var formatCrowdstrikeBoolean = function(col, row) {
    var cell = $('td:eq('+col+')', row),
        value = cell.text().trim();
    
    switch (value) {
        case '1':
            value = '<span class="label label-success">Yes</span>';
            break;
        case '0':
            value = '<span class="label label-danger">No</span>';
            break;
        default:
            value = '<span class="label label-warning">Unknown</span>';
    }
    
    cell.html(value);
}

// Functions are defined and available

// Make sure functions are in global scope
window.formatCrowdstrikeRFMStatus = formatCrowdstrikeRFMStatus;
window.formatCrowdstrikeSafeMode = formatCrowdstrikeSafeMode;
window.formatCrowdstrikeGlobalStatus = formatCrowdstrikeGlobalStatus;
window.formatCrowdstrikeBoolean = formatCrowdstrikeBoolean;

// Functions are available when document is ready
$(document).ready(function() {
    // Formatter functions are ready
});

// Filters for System Extension widget deep links
var sysext_filter = function(colNumber, d){
    if(d.search.value.match(/^sysext_activated$/)){
        // system_extension_activated = 1
        d.columns[colNumber].search.value = '= 1';
        d.search.value = '';
    }
    if(d.search.value.match(/^sysext_present$/)){
        // present but not activated: system_extension_present = 1 AND system_extension_activated != 1
        d.columns[colNumber].search.value = '= 1 && system_extension_activated = 0';
        d.search.value = '';
    }
    if(d.search.value.match(/^sysext_absent$/)){
        // absent: system_extension_present = 0 (do not treat NULL as absent)
        d.columns[colNumber].search.value = '= 0';
        d.search.value = '';
    }
}