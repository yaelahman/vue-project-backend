<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_user_menu', function (Blueprint $table) {
            $table->bigIncrements('id_m_user_menu');
            $table->string('m_user_menu_name')->nullable();
            $table->text('m_user_menu_icon')->nullable();
            $table->char('m_user_menu_status', 1)->nullable();
            $table->unsignedBigInteger('id_m_user_role');
            $table->timestamps();

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
        Schema::dropIfExists('m_user_menu');
    }
}
