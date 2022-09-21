<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToMPersonels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_personel', function (Blueprint $table) {
            $table->string('m_personel_username')->unique()->nullable();
            $table->string('m_personel_password')->nullable();
            $table->string('m_personel_password_show')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_personel', function (Blueprint $table) {
            $table->dropColumn('m_personel_username');
            $table->dropColumn('m_personel_password');
            $table->dropColumn('m_personel_password_show');
        });
    }
}
