<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameHiddenContentColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zone_contents', function (Blueprint $table) {
            $table->renameColumn('hidden', 'media_hidden');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zone_contents', function (Blueprint $table) {
            $table->renameColumn('media_hidden', 'hidden');
        });
    }
}
