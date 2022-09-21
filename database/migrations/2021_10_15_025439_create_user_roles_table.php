<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_user_role', function (Blueprint $table) {
            $table->bigIncrements('id_m_user_role');
            $table->string('m_user_role_name')->nullable();
            $table->dateTime('m_user_role_addBy')->nullable();
            $table->dateTime('m_user_role_inputAt')->nullable();
            $table->char('m_user_role_status', 1)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_user_role');
    }
}
