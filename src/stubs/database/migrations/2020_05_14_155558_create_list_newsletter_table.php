<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListNewsletterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('list_newsletter', function (Blueprint $table) {
            $table->integer('list_id')->unsigned();
            //$table->foreign('list_id')->references('id')->on('lists')->onDelete('cascade');
            $table->integer('newsletter_id')->unsigned();
            //$table->foreign('newsletter_id')->references('id')->on('newsletters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('list_newsletter');
    }
}
