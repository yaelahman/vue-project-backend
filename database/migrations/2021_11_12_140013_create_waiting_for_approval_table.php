<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWaitingForApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_waiting_for_approval', function (Blueprint $table) {
            $table->bigIncrements('id_t_waiting_for_approval');
            $table->unsignedBigInteger('id_m_user_company');
            $table->unsignedInteger('id_t_absensi');
            $table->integer('t_waiting_for_approval_status')->nullable();
            $table->timestamps();

            $table->foreign('id_m_user_company')->references('id_m_user_company')->on('m_user_company')->onDelete('cascade');
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
        Schema::dropIfExists('t_waiting_for_approval');
    }
}
