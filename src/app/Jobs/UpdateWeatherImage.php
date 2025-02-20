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
use Storage;
use Imagick;
use Exception;

class UpdateWeatherImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $jobmarker;
    protected $user;
    protected $zone;
    protected $stationcode;
    protected $entry;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Zone $zone, string $stationcode, QueuedJob $jobmarker, ZoneContent $entry = null)
    {
        $this->zone = $zone;
	    $this->user = $user;
	    $this->stationcode = $stationcode;
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
	        $this->jobmarker->statusUpdate(1, "Querying the weather station...");

            try {

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,"https://api.weather.gov/stations/".$this->stationcode);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, "DigitalSignage/1.0 - ".config("digitalsignage.weather_api_key"));
                $stationinfo = curl_exec($ch);
                curl_close($ch);

                $station = json_decode($stationinfo);
                $stationname = $station->properties->name;
            } catch (Exception $ex) {
                //unable to download
                echo "[Job ".$this->jobmarker->id."] Unable to fetch station data!\n";
                $this->jobmarker->markFailed();
                return; //todo
            }

            $this->jobmarker->statusUpdate(4, "Querying the last update...");

            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,"https://api.weather.gov/stations/".$this->stationcode."/observations/latest");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, "DigitalSignage/1.0 - ".config("digitalsignage.weather_api_key"));
                $obsinfo = curl_exec($ch);
                curl_close($ch);

                $obs = json_decode($obsinfo);
                $conditions = $obs->properties->textDescription;
                $tempC = $obs->properties->temperature->value;
                $tempF = ($tempC * 9/5) + 32;
                $humidity = round($obs->properties->relativeHumidity->value);
                $timestamptext = $obs->properties->timestamp;
            } catch (Exception $ex) {
                //unable to download
                echo "[Job ".$this->jobmarker->id."] Unable to fetch station data!\n";
                $this->jobmarker->markFailed();
                return; //todo
            }

            $image = imagecreatetruecolor(1920, 1080);
            $white = imagecolorallocate($image, 255, 255, 255);
            
            imagettftext($image, 90, 0, 75, 160, $white, "/var/www/html/storage/text.ttf", "Current Weather Conditions");

            switch($conditions) {
                case "Light Rain":
                case "Rain":
                case "Heavy Rain":
                case "Rain and Fog/Mist":

                case "Light Snow":
                case "Snow":
                case "Heavy Snow":

                case "Clear":
                case "Fair":

                case "Cloudy":

                
            }
            imagettftext($image, 200, 0, 100, 645, $white, "/var/www/html/storage/icons.ttf", "\u{f744}");

            imagettftext($image, 50, 0, 500, 290, $white, "/var/www/html/storage/text.ttf", $stationname);
            imagettftext($image, 75, 0, 500, 490, $white, "/var/www/html/storage/text.ttf", $conditions);
            imagettftext($image, 75, 0, 500, 590, $white, "/var/www/html/storage/text.ttf", $tempF."°F");
            imagettftext($image, 75, 0, 500, 690, $white, "/var/www/html/storage/text.ttf", $humidity."% humidity");

            $timesince = \Carbon\Carbon::parse($timestamptext)->diffForHumans(\Carbon\Carbon::now());
            imagettftext($image, 40, 0, 500, 800, $white, "/var/www/html/storage/text.ttf", "Last observation was ".$timesince);

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

            $content->dynamic_type = "weather";
            $content->dynamic_source = $this->stationcode;
            $content->last_dynamic_update = \Carbon\Carbon::now();
            $content->dynamic_update_frequency = 30;
            $content->media_name = "Weather from ".$this->stationcode;
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
