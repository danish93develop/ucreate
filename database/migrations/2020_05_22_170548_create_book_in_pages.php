<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookInPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_in_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('book_id')->nullable(false);
            $table->bigInteger('page_no')->nullable(false);
            $table->double('page_size_in_KB')->nullable(false);
            $table->text('content');
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
        Schema::dropIfExists('book_in_pages');
    }
}
