<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class AddSensorOperational extends Migration
{
    private $tableName = 'crowdstrike';

    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->integer('sensor_operational')->nullable();
        });
        
        // Create indexes
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->index('sensor_operational');
        });
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table('crowdstrike', function (Blueprint $table) {
            $table->dropIndex('sensor_operational');
            $table->dropColumn('sensor_operational');
        });
    }
}
