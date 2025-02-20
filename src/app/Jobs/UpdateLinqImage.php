<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Zone;
use App\Models\User;
use App\Models\QueuedJob;
use App\Models\ZoneContent;
use App\Models\LinqDataSource;
use Storage;
use Imagick;
use Exception;

class UpdateLinqImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $jobmarker;
    protected $user;
    protected $zone;
    protected $menuid;
    protected $entry;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Zone $zone, string $menuid, QueuedJob $jobmarker, ZoneContent $entry = null)
    {
        $this->zone = $zone;
	    $this->user = $user;
	    $this->menuid = $menuid;
	    $this->jobmarker = $jobmarker;
        $this->entry = $entry;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
	        $this->jobmarker->statusUpdate(10, "Querying the Linq server for the menu identifiers...");

            $lds = new LinqDataSource();

            try {
                $menuIds = $lds->getMenuDataFromId($this->menuid);
                if (!$menuIds) {
                    throw new Exception;
                }
            } catch (Exception $ex) {
                //unable to download
                echo "[Job ".$this->jobmarker->id."] Unable to lookup menu short ID (".$this->menuid.")!\n";
                $this->jobmarker->markFailed();
                return; //todo
            }

            $this->jobmarker->statusUpdate(20, "Querying the Linq server for the menu...");

            try {
                $lds = new LinqDataSource();
                $date = date('m-d-Y');
                $menuData = $lds->getMenuForDay($menuIds['district_id'], $menuIds['building_id'], $date);
                if (!$menuData) {
                    $noMenu = true;
                } else {
                    $noMenu = false;
                    $menuMeal = $menuData["Entree"];
                }
            } catch (Exception $ex) {
                //unable to download
                echo "[Job ".$this->jobmarker->id."] Unable to fetch menu data!\n";
                $this->jobmarker->markFailed();
                return; //todo
            }

            $this->jobmarker->statusUpdate(30, "Creating the image...");

            $image = imagecreatetruecolor(1920, 1080);
            $white = imagecolorallocate($image, 255, 255, 255);
            
            imagettftext($image, 90, 0, 75, 160, $white, "/var/www/html/storage/text.ttf", "Today's Lunch Menu");
            imagettftext($image, 50, 0, 75, 290, $white, "/var/www/html/storage/text.ttf", $date." - ".$menuIds['building_name']);

            if ($noMenu) {
                imagettftext($image, 50, 0, 75, 490, $white, "/var/www/html/storage/text.ttf", "Unable to get menu details");
            } else {
                $y = 490;
                foreach($menuMeal as $item) {
                    imagettftext($image, 50, 0, 75, $y, $white, "/var/www/html/storage/text.ttf", $item['name']);
                    $y += 100;
                }
            }

            ob_start();
            imagepng($image);
            $imagedata = ob_get_contents();
            ob_end_clean();
            imagedestroy($image);

            try {
                $filename = $this->generateRandomString(30);
                Storage::put("public/".$filename.".png", $imagedata, 'public');
            } catch (Exception $ex) {
                echo "[Job ".$this->jobmarker->id."] Could not write PNG to disk!";
                $this->jobmarker->markFailed();
                return; //todo
            }

            $folderpath = '/var/www/html/storage/app/public/';
            $fullpath = $folderpath.$filename.'.png';
            $publicurl = url(Storage::url("public/".$filename.".png"));

            if ($this->entry != null) {
                $content = $this->entry;
                $content->prepForDelete(); //delete existing content
                $content->original_url = $fullpath;
		        $content->upload_url = $publicurl;
                $content->fetched = false;
            } else {
                $content = $this->zone->addContent($this->user, "picture", $fullpath, $publicurl);
            }
            
            $content->dynamic_type = "linq";
            $content->dynamic_source = $this->menuid;
            $content->last_dynamic_update = \Carbon\Carbon::now();
            $content->dynamic_update_frequency = 60;
            $content->media_name = "Linq Menu for ".$menuIds['building_name']." on ".$date;
            $content->save();

            $this->jobmarker->statusUpdate(100, "Finished");

        } catch (Exception $ex) {
            echo "[Job ".$this->jobmarker->id."] Generic problem!\n";
            $this->jobmarker->markFailed();
            throw new Exception;
        }

        $this->jobmarker->markDone();
    }

    private function generateRandomString($length = 10) {
    	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	$charactersLength = strlen($characters);
    	$randomString = '';
    	for ($i = 0; $i < $length; $i++) {
        	$randomString .= $characters[rand(0, $charactersLength - 1)];
    	}
    	return $randomString;
    }
}
