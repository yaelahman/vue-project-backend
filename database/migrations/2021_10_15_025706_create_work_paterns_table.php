<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkPaternsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_work_patern', function (Blueprint $table) {
            $table->bigIncrements('id_m_work_patern');
            $table->string('m_work_patern_name')->nullable();
            $table->integer('m_work_patern_numberCycle')->nullable();
            $table->char('m_work_patern_tolerance_status', 1)->nullable();
            $table->integer('m_work_patern_tolerance')->nullable();
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
        Schema::dropIfExists('m_work_patern');
    }
}
