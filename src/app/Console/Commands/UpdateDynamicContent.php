<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ZoneContent;

class UpdateDynamicContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamic:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update any dynamic content';

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
            print("Triggering a dynamic refresh for content id ".$content->id."...\n");
            $content->triggerDynamicRefresh();
        }

        return 0;
    }
}
