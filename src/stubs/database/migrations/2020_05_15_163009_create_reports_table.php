<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('newsletter_id')->unsigned();
            $table->foreign('newsletter_id')->references('id')->on('newsletters')->onDelete('cascade');
            $table->integer('contact_id')->unsigned();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->boolean('delivered')->default(1);
            $table->boolean('opened')->default(0);
            $table->dateTime('opened_at')->nullable();
            $table->boolean('unsubscribed')->default(0);
            $table->text('clicks')->nullable();
            $table->text('error')->nullable();
            $table->string('identifier')->nullable();
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
        Schema::dropIfExists('reports');
    }
}
