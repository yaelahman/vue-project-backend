<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancePersonelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_attendance_personel', function (Blueprint $table) {
            $table->bigIncrements('id_m_attendance_personel');
            $table->unsignedBigInteger('id_m_personel');
            $table->unsignedBigInteger('id_m_attendance_spots');
            $table->unsignedBigInteger('id_m_user_company');
            $table->timestamps();

            $table->foreign('id_m_personel')->references('id_m_personel')->on('m_personel')->onDelete('cascade');
            $table->foreign('id_m_attendance_spots')->references('id_m_attendance_spots')->on('m_attendance_spots')->onDelete('cascade');
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
        Schema::dropIfExists('m_attendance_personel');
    }
}
