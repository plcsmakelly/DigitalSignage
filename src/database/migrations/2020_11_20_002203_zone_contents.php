<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ZoneContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zone_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id');
            $table->integer('order');
            $table->string('media_type')->default('image');
            $table->string('original_url')->nullable();
            $table->string('upload_url')->nullable();
            $table->integer('start_time')->default(0);
            $table->integer('duration')->default(-1);
            $table->foreignId('uploaded_by');
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
        Schema::dropIfExists('zone_contents');
    }
}
