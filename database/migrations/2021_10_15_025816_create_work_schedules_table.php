<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_work_schedule', function (Blueprint $table) {
            $table->bigIncrements('id_m_work_schedule');
            $table->char('m_work_schedule_type', 1)->nullable();
            $table->time('m_work_schedule_clockIn')->nullable();
            $table->time('m_work_schedule_clockOut')->nullable();
            $table->unsignedBigInteger('id_m_work_patern');
            $table->timestamps();

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
        Schema::dropIfExists('m_work_schedule');
    }
}
