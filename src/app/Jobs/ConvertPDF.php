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
use App\Models\GoogleToken;
use Storage;
use Imagick;
use Exception;

class ConvertPDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $jobmarker;
    protected $user;
    protected $zone;
    protected $pdf_path;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Zone $zone, string $pdf_path, QueuedJob $jobmarker)
    {
        $this->zone = $zone;
		$this->user = $user;
		$this->pdf_path = $pdf_path;
		$this->jobmarker = $jobmarker;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    	echo "[Job ".$this->jobmarker->id."] Begin job action for user ".$this->user->id.", original PDF is ".$this->pdf_path."...";

		$folderpath = '/var/www/html/storage/app/public/';

		try {
			$this->jobmarker->statusUpdate(2, "Now starting conversion...");

			$fullpath = $this->pdf_path;
			echo "[Job ".$this->jobmarker->id."] PDF should be available at ".$fullpath."\n";

			//first get the number of pages needed to convert
			$image = new Imagick();
			$image->pingImage($fullpath);
			$pagecount = $image->getNumberImages() - 1; //minus 1 for a zero-indexed array

			echo "[Job ".$this->jobmarker->id."] This PDF has ".($pagecount + 1)." pages.\n";

			$this->jobmarker->statusUpdate(round((5/6+$pagecount), 0), "Processing pages...");

			$count = 0;
			while($count <= $pagecount) {
				$this->jobmarker->statusUpdate(round(($count/6+$pagecount), 0), "Processing page ".$count);
				$pagefilename = $this->generateRandomString(30);
				$image = new Imagick();
				$image->setResolution(150,150);

				echo "[Job ".$this->jobmarker->id."] Now processing ".$fullpath.'['.$count.']'." with count ".$count."\n";

				$image->readImage($fullpath.'['.$count.']');
				$image->setImageFormat('png');
				$image->setImageBackgroundColor('white');
				$image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
				$image->writeImage($folderpath.$pagefilename.".png");
				echo "[Job ".$this->jobmarker->id."] Page ".$count." is now available as PNG at ".$folderpath.$pagefilename.".png\n";

				$content = $this->zone->addContent($this->user, "picture", $folderpath.$pagefilename.'.png', asset('storage/'.$pagefilename.'.png'));
				$content->dynamic_type = "pdf";
				$content->dynamic_source = "";
				$content->last_dynamic_update = \Carbon\Carbon::now();
				$content->dynamic_update_frequency = 0;
				$content->save();
				$count++;
        	}

			echo "[Job ".$this->jobmarker->id."] Deleting the PDF as we are finished.\n";
			Storage::delete($fullpath);

			$this->jobmarker->statusUpdate(100, "Finished");
		} catch (Exception $ex) {
			echo "[Job ".$this->jobmarker->id."] Generic problem!\n";
			//error_log($ex);
			//var_dump($ex);
			$this->jobmarker->markFailed();
			throw new Exception($ex->getMessage());
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
