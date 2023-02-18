<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class CrowdstrikeInit extends Migration
{
    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->create('crowdstrike', function (Blueprint $table) {
            $table->integer('sensor_active')->nullable();

            $table->index('sensor_active');
        });
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table('crowdstrike', function (Blueprint $table) {
            $table->dropColumn('sensor_active');
        });    }
}
