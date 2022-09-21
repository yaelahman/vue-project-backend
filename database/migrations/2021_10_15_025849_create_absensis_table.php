<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsensisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_absensi', function (Blueprint $table) {
            $table->bigIncrements('id_t_absensi');
            $table->date('t_absensi_Dates')->nullable();
            $table->timestamp('t_absensi_startClock')->nullable();
            $table->timestamp('t_absensi_endClock')->nullable();
            $table->integer('t_absensi_status')->nullable();
            $table->text('t_absensi_catatan')->nullable();
            $table->integer('t_absensi_isLate')->nullable();
            $table->string('t_absensi_latLong')->nullable();
            $table->string('t_absensi_latLongEnd')->nullable();
            $table->integer('t_absensi_approval')->default(0);
            $table->unsignedBigInteger('id_m_personel');
            $table->unsignedBigInteger('id_m_user_company');
            $table->timestamps();

            $table->foreign('id_m_personel')->references('id_m_personel')->on('m_personel')->onDelete('cascade');
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
        Schema::dropIfExists('t_absensi');
    }
}
