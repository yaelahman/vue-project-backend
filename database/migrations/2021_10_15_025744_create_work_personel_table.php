<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkPersonelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_work_personel', function (Blueprint $table) {
            $table->bigIncrements('id_m_work_personel');
            $table->unsignedBigInteger('id_m_personel');
            $table->unsignedBigInteger('id_m_work_patern');
            $table->dateTime('m_work_personel_time')->nullable();
            $table->unsignedBigInteger('id_m_user_company')->nullable();
            $table->timestamps();

            $table->foreign('id_m_user_company')->references('id_m_user_company')->on('m_user_company')->onDelete('cascade');
            $table->foreign('id_m_personel')->references('id_m_personel')->on('m_personel')->onDelete('cascade');
            $table->foreign('id_m_work_patern')->references('id_m_work_patern')->on('m_work_patern')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_work_personel');
    }
}
