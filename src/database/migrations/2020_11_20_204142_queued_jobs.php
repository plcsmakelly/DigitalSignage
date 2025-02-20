<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class QueuedJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queued_jobs', function (Blueprint $table) {
            $table->id();
            $table->boolean('finished')->default(false);
            $table->boolean('failed')->default(false);
            $table->integer('percent')->default(0);
            $table->string('message')->default("");
            $table->string('redirect_to')->default("");
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
        Schema::dropIfExists('queued_jobs');
    }
}
