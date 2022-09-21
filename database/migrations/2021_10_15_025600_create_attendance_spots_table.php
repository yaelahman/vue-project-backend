<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceSpotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_attendance_spots', function (Blueprint $table) {
            $table->bigIncrements('id_m_attendance_spots');
            $table->string('m_attendance_spots_address')->nullable();
            $table->string('m_attendance_spots_latitude')->nullable();
            $table->string('m_attendance_spots_longitude')->nullable();
            $table->string('m_attendance_spots_name')->nullable();
            $table->integer('m_attendance_spots_radius')->nullable();
            $table->unsignedBigInteger('id_m_user_company')->nullable();
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
        Schema::dropIfExists('m_attendance_spots');
    }
}
