<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class CrowdstrikeV5EnhancedFields extends Migration
{
    private $tableName = 'crowdstrike';

    public function up()
    {
        $capsule = new Capsule();
        $schema = $capsule::schema();

        // Enhanced security and compliance fields - each with conditional check
        if (! $schema->hasColumn($this->tableName, 'falcon_full_disk_access')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_full_disk_access')->nullable();
                $table->index('falcon_full_disk_access');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_system_extension_approved')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_system_extension_approved')->nullable();
                $table->index('falcon_system_extension_approved');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_network_filter_active')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_network_filter_active')->nullable();
                $table->index('falcon_network_filter_active');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_web_filter_active')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_web_filter_active')->nullable();
                $table->index('falcon_web_filter_active');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_service_management_configured')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_service_management_configured')->nullable();
                $table->index('falcon_service_management_configured');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_team_id_valid')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_team_id_valid')->nullable();
                $table->index('falcon_team_id_valid');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_non_removable_extension')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_non_removable_extension')->nullable();
                $table->index('falcon_non_removable_extension');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_rfm_status')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_rfm_status')->nullable();
                $table->index('falcon_rfm_status');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_safe_mode')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_safe_mode')->nullable();
                $table->index('falcon_safe_mode');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_cloud_connected')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_cloud_connected')->nullable();
                $table->index('falcon_cloud_connected');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_heartbeat_active')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_heartbeat_active')->nullable();
                $table->index('falcon_heartbeat_active');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_network_traffic_filtering')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_network_traffic_filtering')->nullable();
                $table->index('falcon_network_traffic_filtering');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_macos_compatible')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_macos_compatible')->nullable();
                $table->index('falcon_macos_compatible');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_installation_paths_valid')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_installation_paths_valid')->nullable();
                $table->index('falcon_installation_paths_valid');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'falcon_global_status')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('falcon_global_status')->nullable();
                $table->index('falcon_global_status');
            });
        }

        // Additional fields that might be missing
        if (! $schema->hasColumn($this->tableName, 'system_extension_present')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('system_extension_present')->nullable();
                $table->index('system_extension_present');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'system_extension_activated')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('system_extension_activated')->nullable();
                $table->index('system_extension_activated');
            });
        }

        if (! $schema->hasColumn($this->tableName, 'installed')) {
            $schema->table($this->tableName, function (Blueprint $table) {
                $table->integer('installed')->nullable();
                $table->index('installed');
            });
        }
    }

    public function down()
    {
        $capsule = new Capsule();
        $schema = $capsule::schema();

        // Remove all the falcon_* fields we added
        $falconFields = [
            'falcon_full_disk_access',
            'falcon_system_extension_approved',
            'falcon_network_filter_active',
            'falcon_web_filter_active',
            'falcon_service_management_configured',
            'falcon_team_id_valid',
            'falcon_non_removable_extension',
            'falcon_rfm_status',
            'falcon_safe_mode',
            'falcon_cloud_connected',
            'falcon_heartbeat_active',
            'falcon_network_traffic_filtering',
            'falcon_macos_compatible',
            'falcon_installation_paths_valid',
            'falcon_global_status'
        ];

        // Additional fields we might have added
        $additionalFields = [
            'system_extension_present',
            'system_extension_activated',
            'installed'
        ];

        $schema->table($this->tableName, function (Blueprint $table) use ($schema, $falconFields, $additionalFields) {
            foreach ($falconFields as $field) {
                if ($schema->hasColumn($this->tableName, $field)) {
                    $table->dropColumn($field);
                }
            }
            foreach ($additionalFields as $field) {
                if ($schema->hasColumn($this->tableName, $field)) {
                    $table->dropColumn($field);
                }
            }
        });
    }
}
