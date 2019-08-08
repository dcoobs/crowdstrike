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
            $table->increments('id');
            $table->string('serial_number')->unique();
            $table->string('sensor_id')->nullable();
            $table->string('sensor_version')->nullable();
            $table->string('customer_id')->nullable();
            $table->integer('sensor_installguard')->nullable();

            $table->index('serial_number');
            $table->index('sensor_id');
            $table->index('sensor_version');
            $table->index('customer_id');
            $table->index('sensor_installguard');
        });
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->dropIfExists('crowdstrike');
    }
}
