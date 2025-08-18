<?php

use munkireport\processors\Processor;
use CFPropertyList\CFPropertyList;

class Crowdstrike_processor extends Processor
{
    public function run($data)
    {
        // Parse client data
        $parsedData = [];
        
        try {
            // Parse plist data
            $parser = new CFPropertyList();
            $parser->parse($data, CFPropertyList::FORMAT_XML);
            $parsedData = $parser->toArray();
        } catch (Exception $e) {
            // Log error for debugging but don't expose details
            error_log("CrowdStrike: Error parsing client data: " . $e->getMessage());
            // If parsing fails, return early
            return $this;
        }
        
        // Create model instance with serial number
        $model = new Crowdstrike_model($this->serial_number);
        
        // Set fields from client data only if they are present
        foreach ($parsedData as $field => $value) {
            if (array_key_exists($field, $model->rs)) {
                $model->$field = $value;
            }
        }
        
        // Calculate falcon_global_status (server-side calculation)
        $this->calculateGlobalStatus($model);
        
        // Save the model
        try {
            $model->save();
        } catch (Exception $e) {
            // Log error for debugging but don't expose details
            error_log("CrowdStrike: Error saving data for serial {$this->serial_number}: " . $e->getMessage());
        }
        
        return $this;
    }
    
    /**
     * Calculate falcon_global_status based on current model data
     * Only calculates if installed = 1, otherwise leaves global status unchanged
     */
    private function calculateGlobalStatus($model)
    {
        // Only calculate global status if CrowdStrike is installed
        if ($model->installed != 1) {
            // Skip calculation and leave global status unchanged
            return;
        }
        
        // Get all fields that should be 1 for OK status
        $sensor_active = $model->sensor_active;
        $sensor_installguard = $model->sensor_installguard;
        $falcon_full_disk_access = $model->falcon_full_disk_access;
        $falcon_system_extension_approved = $model->falcon_system_extension_approved;
        $falcon_network_filter_active = $model->falcon_network_filter_active;
        $falcon_web_filter_active = $model->falcon_web_filter_active;
        $falcon_service_management_configured = $model->falcon_service_management_configured;
        $falcon_team_id_valid = $model->falcon_team_id_valid;
        $falcon_non_removable_extension = $model->falcon_non_removable_extension;
        $falcon_cloud_connected = $model->falcon_cloud_connected;
        $falcon_heartbeat_active = $model->falcon_heartbeat_active;
        $falcon_network_traffic_filtering = $model->falcon_network_traffic_filtering;
        $falcon_macos_compatible = $model->falcon_macos_compatible;
        $falcon_installation_paths_valid = $model->falcon_installation_paths_valid;
        
        // Get fields that should be 0 for OK status
        $falcon_rfm_status = $model->falcon_rfm_status;
        $falcon_safe_mode = $model->falcon_safe_mode;
        
        // Only calculate if we have some falcon data
        if (!is_null($falcon_full_disk_access) || !is_null($falcon_system_extension_approved) || $model->installed == 1) {
            // Check if ALL fields that should be 1 are actually 1
            $all_positive_ok = ($sensor_active === 1 && 
                                $sensor_installguard === 1 &&
                                $falcon_full_disk_access === 1 && 
                                $falcon_system_extension_approved === 1 && 
                                $falcon_network_filter_active === 1 && 
                                $falcon_web_filter_active === 1 && 
                                $falcon_service_management_configured === 1 && 
                                $falcon_team_id_valid === 1 &&
                                $falcon_non_removable_extension === 1 &&
                                $falcon_cloud_connected === 1 &&
                                $falcon_heartbeat_active === 1 &&
                                $falcon_network_traffic_filtering === 1 &&
                                $falcon_macos_compatible === 1 &&
                                $falcon_installation_paths_valid === 1);
            
            // Check if fields that should be 0 are actually 0
            $all_negative_ok = ($falcon_rfm_status === 0 && $falcon_safe_mode === 0);
            
            // Set the global status: 1 (OK) only if ALL required fields have correct values
            $model->falcon_global_status = ($all_positive_ok && $all_negative_ok) ? 1 : 0;
        }
    }
}