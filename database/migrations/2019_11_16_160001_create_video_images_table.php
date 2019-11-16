<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('video_id')->unsigned();
            $table->string('name');
            $table->string('imgpath')->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('video_id')
                ->references('id')
                ->on('videos')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_images');
    }
}
