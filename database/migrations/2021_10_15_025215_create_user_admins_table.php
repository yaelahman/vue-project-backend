<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_user_admin', function (Blueprint $table) {
            $table->bigIncrements('id_m_user_admin');
            $table->string('m_user_admin_email')->nullable();
            $table->text('m_user_admin_password')->nullable();
            $table->char('m_user_admin_status', 1)->nullable();
            $table->dateTime('m_user_admin_lastLogin')->nullable();
            $table->dateTime('m_user_admin_lastLogout')->nullable();
            $table->unsignedBigInteger('id_m_user_company');
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
        Schema::dropIfExists('m_user_admin');
    }
}
