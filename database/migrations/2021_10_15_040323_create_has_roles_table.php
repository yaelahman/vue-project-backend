<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHasRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_has_roles', function (Blueprint $table) {
            $table->bigIncrements('id_m_has_roles');
            $table->unsignedBigInteger('id_m_user_admin');
            $table->unsignedBigInteger('id_m_user_role');
            $table->timestamps();

            $table->foreign('id_m_user_admin')->references('id_m_user_admin')->on('m_user_admin')->onDelete('cascade');
            $table->foreign('id_m_user_role')->references('id_m_user_role')->on('m_user_role')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_has_roles');
    }
}
