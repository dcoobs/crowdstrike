<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class RemoveSensorOperational extends Migration
{
    private $tableName = 'crowdstrike';
    
    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropIndex('sensor_operational');
            $table->dropColumn('sensor_operational');
        });
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->create('crowdstrike', function (Blueprint $table) {
            $table->string('sensor_operational')->nullable();
            $table->index('sensor_operational');
        });
        
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->index('sensor_operational');
        });
    }
}
