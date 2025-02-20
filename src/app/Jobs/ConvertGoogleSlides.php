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

class ConvertGoogleSlides implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $jobmarker;
    protected $user;
    protected $zone;
    protected $presentation_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Zone $zone, string $presentation_id, QueuedJob $jobmarker)
    {
        $this->zone = $zone;
		$this->user = $user;
		$this->presentation_id = $presentation_id;
		$this->jobmarker = $jobmarker;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		echo "[Job ".$this->jobmarker->id."] Begin job action for user ".$this->user->id.", requested URL is ".$this->presentation_id."...";

		try {
			$this->jobmarker->statusUpdate(1, "Retrieving Google access token for user...");

			$token = GoogleToken::getForUser($this->user->id);
			if (!$token->isCorrectlyLinked()) {
				$this->jobmarker->markFailed();
				return; //todo
			}
			$client = $token->getAPIObject();

			echo "[Job ".$this->jobmarker->id."] Now processing presentation ID ".$this->presentation_id."\n";
			$this->jobmarker->statusUpdate(2, "Now starting processing...");

			try {
				$driveService = new \Google_Service_Drive($client);
				$file = $driveService->files->get($this->presentation_id, array('supportsAllDrives' => true, 'fields' => 'name, exportLinks'));
				$links = $file->getExportLinks();
				$pdflink = $links["application/pdf"];
				if (!$pdflink || $pdflink == "") {
					echo "[Job ".$this->jobmarker->id."] Unable to find export download link from Google! (NO PDF AVAILABLE)\n";
					$this->jobmarker->markFailed();
					return;
					//throw new Exception("No PDF link was available!");
				}
				echo "[Job ".$this->jobmarker->id."] Received download link from Google Drive: ".$pdflink."\n";
			} catch (Exception $ex) {
				//unable to get the download link
				echo "[Job ".$this->jobmarker->id."] Unable to find export download link from Google!\n";
				//var_dump($ex);
				$this->jobmarker->markFailed();
				return; //todo
			}

			$this->jobmarker->statusUpdate(3, "Successfully retrieved information about file from Google...");

			try {
				$httpClient = $client->authorize();
				$this->jobmarker->statusUpdate(3, "Downloading the content from Google...");
				$response = $httpClient->get($pdflink);
				$pdfcontents = $response->getBody()->getContents();
				echo "[Job ".$this->jobmarker->id."] Downloaded the PDF contents for item: ".$this->presentation_id."\n";
			} catch (Exception $ex) {
				//unable to download
				echo "[Job ".$this->jobmarker->id."] Unable to download the contents from Google!\n";
				//var_dump($ex);
				$this->jobmarker->markFailed();
				return; //todo
			}

			$this->jobmarker->statusUpdate(4, "Downloaded contents of data from Google...");

			try {
				$filename = $this->generateRandomString(30);
				Storage::put("public/".$filename.".pdf", $pdfcontents, 'public');
			} catch (Exception $ex) {
				//failed to save PDF to disk
				echo "[Job ".$this->jobmarker->id."] Could not write PDF to disk!";
				//var_dump($ex);
				$this->jobmarker->markFailed();
				return; //todo
			}

			$this->jobmarker->statusUpdate(5, "Now ready to convert pages from Google to images...");

			$folderpath = '/var/www/html/storage/app/public/';
			$fullpath = $folderpath.$filename.'.pdf';

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
				$content->dynamic_type = "googleslides";
				$content->dynamic_source = $this->presentation_id;
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
