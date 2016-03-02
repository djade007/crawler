<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->text('author');
            $table->string('title')->nullable();
            $table->integer('parent_id')->unsigned()->nullable();
            $table->text('content');
            $table->integer('c_id')->unsigned()->unique();
            $table->enum('type', ['nairaland', 'stackoverflow']);
            $table->integer('views');
            $table->string('link');
            $table->text('data');
            $table->timestamp('date');
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
        Schema::drop('posts');
    }
}
