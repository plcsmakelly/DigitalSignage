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
use Storage;
use Imagick;
use Exception;

class FetchInformationText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $jobmarker;
    protected $user;
    protected $zone;
    protected $infotext;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Zone $zone, string $infotext, QueuedJob $jobmarker)
    {
        $this->zone = $zone;
	    $this->user = $user;
	    $this->infotext = $infotext;
	    $this->jobmarker = $jobmarker;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
	        $this->jobmarker->statusUpdate(1, "Requesting the text generation...");

            try {
                //download the content
                $data = array(
                    "alert_text" => $this->infotext,
                    "header_text" => "Information",
                    "header_icon" => "0xf05a",
                    "header_color" => "#0a84ff",
                    "repeat_count" => "1",
                    "enable_alert_tone" => "false",
                    "enable_tts" => "false"
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,"http://videogen:5000/generator");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $pdfcontents = curl_exec($ch);
                curl_close($ch);

                echo "[Job ".$this->jobmarker->id."] Downloaded the video contents for item.\n";
            } catch (Exception $ex) {
                //unable to download
                echo "[Job ".$this->jobmarker->id."] Unable to download the contents from VideoGen!\n";
                $this->jobmarker->markFailed();
                return; //todo
            }

            $this->jobmarker->statusUpdate(4, "Downloaded contents...");

            try {
                $filename = $this->generateRandomString(30);
                Storage::put("public/".$filename.".mp4", $pdfcontents, 'public');
            } catch (Exception $ex) {
                echo "[Job ".$this->jobmarker->id."] Could not write MP4 to disk!";
                $this->jobmarker->markFailed();
                return; //todo
            }

            $folderpath = '/var/www/html/storage/app/public/';
            $fullpath = $folderpath.$filename.'.mp4';
            $publicurl = url(Storage::url("public/".$filename.".mp4"));

            $content = $this->zone->addContent($this->user, "video", $fullpath, $publicurl);
            $content->dynamic_type = "text";
            $content->dynamic_source = "";
            $content->last_dynamic_update = \Carbon\Carbon::now();
            $content->dynamic_update_frequency = 0;
            $content->media_name = "Text: ".substr($this->infotext, 0, 20)."...";
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
