<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRtdFieldsToZoneContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zone_contents', function (Blueprint $table) {
            $table->boolean("hidden")->default(false);

            $table->string("dynamic_type")->nullable();
            $table->string("dynamic_source")->nullable();
            $table->timestamp('last_dynamic_update')->nullable();
            $table->integer('dynamic_update_frequency')->default(30); //minutes
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
            $table->dropColumn("hidden");
            $table->dropColumn("dynamic_type");
            $table->dropColumn("dynamic_source");
            $table->dropColumn("last_dynamic_update");
            $table->dropColumn("dynamic_update_frequency");
        });
    }
}
