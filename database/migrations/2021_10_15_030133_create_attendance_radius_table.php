<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRadiusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_attendance_radius', function (Blueprint $table) {
            $table->bigIncrements('id_m_attendance_radius');
            $table->string('m_attendance_radius_name')->nullable();
            $table->char('m_attendance_radius_status', 1)->nullable();
            $table->string('m_attendance_radius_addBy')->nullable();
            $table->dateTime('m_attendance_radius_inputDate')->nullable();
            $table->unsignedBigInteger('id_m_attendance_spots');
            $table->timestamps();

            $table->foreign('id_m_attendance_spots')->references('id_m_attendance_spots')->on('m_attendance_spots')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_attendance_radius');
    }
}
