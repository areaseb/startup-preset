<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');

            $table->string('nome');
            $table->string('cognome');
            $table->string('cellulare')->nullable();
            $table->string('email')->nullable();

            $table->string('indirizzo')->nullable();
            $table->string('cap', 10)->nullable();
            $table->string('citta')->nullable();
            $table->string('provincia')->nullable();
            $table->integer('city_id')->unsigned()->nullable();
            $table->char('nazione', 2)->default('IT');
            $table->char('lingua', 2)->default('it');

            $table->boolean('subscribed')->default(true);
            $table->boolean('requested_unsubscribed')->default(false);
            $table->string('tipo')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('company_id')->unsigned()->nullable();

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
        Schema::dropIfExists('contacts');
    }
}
