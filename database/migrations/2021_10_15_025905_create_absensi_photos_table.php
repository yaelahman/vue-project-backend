<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsensiPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_absensi_photo', function (Blueprint $table) {
            $table->bigIncrements('id_t_absensi_photo');
            $table->text('t_absensi_photopath')->nullable();
            $table->text('t_absensi_photofileOri')->nullable();
            $table->text('t_absensi_photofileSystem')->nullable();
            $table->dateTime('t_absensi_photo_date')->nullable();
            $table->unsignedBigInteger('id_t_absensi')->nullable();
            $table->timestamps();

            $table->foreign('id_t_absensi')->references('id_t_absensi')->on('t_absensi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_absensi_photo');
    }
}
