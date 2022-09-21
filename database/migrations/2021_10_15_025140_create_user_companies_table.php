<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_user_company', function (Blueprint $table) {
            $table->bigIncrements('id_m_user_company');
            $table->string('m_user_company_name')->nullable();
            $table->string('m_user_company_phone', 13)->nullable();
            $table->string('m_user_company_email', 150)->nullable();
            $table->integer('m_user_company_total_personel')->nullable();
            $table->string('m_user_company_timeZone', 50)->nullable();
            $table->dateTime('m_user_company_joinDate')->nullable();
            $table->unsignedBigInteger('id_m_company_industri')->nullable();
            $table->timestamps();

            $table->foreign('id_m_company_industri')->references('id_m_company_industri')->on('m_company_industri')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_user_company');
    }
}
