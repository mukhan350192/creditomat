<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMfoDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mfo_details', function (Blueprint $table) {
            $table->id();
            $table->integer('mfo_id');
            $table->string('description');
            $table->string('background_img');
            $table->string('phone');
            $table->string('documents');
            $table->string('address');
            $table->string('email');
            $table->string('url');
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
        Schema::dropIfExists('mfo_details');
    }
}
