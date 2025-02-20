<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmergencyAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emergency_alerts', function (Blueprint $table) {
            $table->id();

            $table->text('text');
            $table->boolean('enable_alert_tone')->default(true);
            $table->boolean('enable_tts')->default(false);

            $table->string('status')->default("ACTIVE");
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('created_by')->default(0);

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
        Schema::dropIfExists('emergency_alerts');
    }
}
