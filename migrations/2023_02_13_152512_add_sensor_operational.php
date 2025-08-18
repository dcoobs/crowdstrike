<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Schema;

class AddSensorOperational extends Migration
{
    private $tableName = 'crowdstrike';

    public function up()
    {
        $capsule = new Capsule();
        // Add columns only if they don't exist
        if (! $capsule::schema()->hasColumn($this->tableName, 'sensor_active')) {
            $capsule::schema()->table($this->tableName, function (Blueprint $table) {
                $table->integer('sensor_active')->nullable();
                $table->index('sensor_active');
            });
        }
        if (! $capsule::schema()->hasColumn($this->tableName, 'sensor_operational')) {
            $capsule::schema()->table($this->tableName, function (Blueprint $table) {
                $table->string('sensor_operational')->nullable();
                $table->index('sensor_operational');
            });
        }
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table('crowdstrike', function (Blueprint $table) {
            if (Schema::hasColumn('crowdstrike', 'sensor_active')) {
                $table->dropColumn('sensor_active');
            }
            if (Schema::hasColumn('crowdstrike', 'sensor_operational')) {
                $table->dropColumn('sensor_operational');
            }
        });
    }
}
