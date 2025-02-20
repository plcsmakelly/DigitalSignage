<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ZoneContent;

class UpdateDynamicScheduled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamic:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update any dynamic content needing update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $contents = ZoneContent::whereNotNull('dynamic_type')->whereNotNull('dynamic_update_frequency')->where('dynamic_update_frequency', '>', 0)->get();

        foreach($contents as $content) {
            $shouldUpdate = false;

            if ($content->last_dynamic_update != null) {
                $lastDynamicUpdate = new \Carbon\Carbon($content->last_dynamic_update);
                $shouldUpdateTime = $lastDynamicUpdate->addMinutes($content->dynamic_update_frequency);
                $shouldUpdate = $shouldUpdateTime->isPast();
            } else {
                $shouldUpdate = true;
            }

            if ($shouldUpdate) {
                print("Triggering a dynamic refresh for content id ".$content->id."...\n");
                $content->triggerDynamicRefresh();
            } else {
                print("Content ".$content->id." is still good until ".$shouldUpdateTime."\n");
            }
        }

        return 0;
    }
}
