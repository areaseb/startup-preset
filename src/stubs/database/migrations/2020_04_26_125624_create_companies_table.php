<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');

            $table->string('rag_soc');
            $table->string('indirizzo');
            $table->string('cap');
            $table->string('citta');
            $table->string('provincia');
            $table->integer('city_id')->unsigned()->nullable();
            $table->char('nazione', 2)->default('IT');
            $table->char('lingua', 2)->default('it');

            $table->string('pec')->nullable();
            $table->string('piva')->nullable();
            $table->string('cf')->nullable();
            $table->string('sdi')->nullable();

            $table->string('tipo')->nullable();
            $table->boolean('fornitore')->default(0);
            $table->boolean('partner')->default(0);
            $table->boolean('attivo')->default(1);

            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('email_ordini')->nullable();
            $table->string('email_fatture')->nullable();

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
        Schema::dropIfExists('companies');
    }
}
