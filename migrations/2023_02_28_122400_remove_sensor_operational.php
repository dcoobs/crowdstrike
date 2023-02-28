<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class RemoveSensorOperational extends Migration
{
    private $tableName = 'crowdstrike';
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table('crowdstrike', function (Blueprint $table) {
            $table->dropIndex('sensor_operational');
            $table->dropColumn('sensor_operational');
        });
    }
}
