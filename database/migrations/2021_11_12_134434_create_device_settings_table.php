<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_device_settings', function (Blueprint $table) {
            $table->bigIncrements('id_m_device_settings');
            $table->unsignedBigInteger('id_m_user_company');
            $table->boolean('m_device_settings_absensiCamera')->nullable()->default(false);
            $table->boolean('m_device_settings_absensiFaceRecognition')->nullable()->default(false);
            $table->boolean('m_device_settings_visitCamera')->nullable()->default(false);
            $table->boolean('m_device_settings_visitFaceRecognition')->nullable()->default(false);
            $table->boolean('m_device_settings_overtimeCamera')->nullable()->default(false);
            $table->boolean('m_device_settings_overtimeFaceRecognition')->nullable()->default(false);
            $table->timestamps();

            $table->foreign('id_m_user_company')->references('id_m_user_company')->on('m_user_company')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_device_settings');
    }
}
