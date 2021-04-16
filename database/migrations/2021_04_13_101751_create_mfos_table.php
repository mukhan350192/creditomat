<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mfos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo');
            $table->integer('amount_min');
            $table->integer('amount_max');
            $table->integer('srok_min');
            $table->integer('srok_max');
            $table->integer('sell_quantity')->default(0);
            $table->integer('stavka');
            $table->integer('approve_percent');
            $table->integer('review_time');
            $table->string('getting_way')->nullable();
            $table->double('rating')->nullable();
            $table->double('delay')->nullable();
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('mfos');
    }
}
