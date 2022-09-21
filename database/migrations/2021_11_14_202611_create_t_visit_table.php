<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTVisitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_visit', function (Blueprint $table) {
            $table->bigIncrements('id_t_visit');
            $table->unsignedBigInteger('id_m_user_company');
            $table->unsignedInteger('id_m_personel');
            $table->date('t_visit_dates')->nullable();
            $table->timestamp('t_visit_in')->nullable();
            $table->timestamp('t_visit_out')->nullable();
            $table->timestamp('t_visit_latLongIn')->nullable();
            $table->timestamp('t_visit_latLongOut')->nullable();
            $table->integer('t_visit_status')->nullable();
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
        Schema::dropIfExists('t_visit');
    }
}
