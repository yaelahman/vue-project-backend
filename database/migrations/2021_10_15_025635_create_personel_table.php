<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_personel', function (Blueprint $table) {
            $table->bigIncrements('id_m_personel');
            $table->string('m_personel_names')->nullable();
            $table->char('m_personel_gender', 1)->nullable();
            $table->string('m_personel_personID', 50)->nullable();
            $table->string('m_personel_email')->nullable();
            $table->text('m_personel_profilePic')->nullable();
            $table->dateTime('m_personel_joinDate')->nullable();
            $table->char('m_personel_status', 1)->nullable();
            $table->char('m_personel_invited', 1)->nullable();
            $table->text('m_personel_faceRecognition')->nullable();
            $table->char('m_personel_tokenLogin', 8)->nullable()->unique();
            $table->char('is_logged_in', 1)->nullable();
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
        Schema::dropIfExists('m_personel');
    }
}
