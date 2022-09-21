<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaceRecognitionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_face_recognition', function (Blueprint $table) {
            $table->bigIncrements('id_t_face_recognition');
            $table->unsignedInteger('id_m_personel');
            $table->unsignedBigInteger('id_m_user_company');
            $table->text('t_face_recognition_image')->nullable();
            $table->text('t_face_recognition_detect')->nullable();
            $table->timestamps();

            $table->foreign('id_m_user_company')->references('id_m_user_company')->on('m_user_company')->onDelete('cascade');
            $table->foreign('id_m_personel')->references('id_m_personel')->on('m_personel')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_face_recognition');
    }
}
