<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Zone;
use App\Models\ZoneContent;
use App\Models\GoogleToken;
use App\Jobs\ConvertGoogleSlides;
use App\Jobs\ConvertPDF;
use App\Jobs\FetchInformationText;
use App\Jobs\UpdateWeatherImage;
use App\Jobs\UpdateLinqImage;
use App\Models\QueuedJob;
use App\Models\LinqDataSource;
use Auth;
use Exception;
use Imagick;

class ZoneAddController extends Controller
{
    public function showZoneAddPicture($id, Request $request) {
	try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

	return view('zones.add.picture', array('title' => 'Add Picture', 'zone' => $zone));
    }

    public function doZoneAddPicture($id, Request $request) {
	try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

	if ($request->hasFile('file')) {
                $path = $request->file('file')->storePublicly('public');
                $publicurl = url(Storage::url($path));

                $zone->addContent(Auth::user(), "picture", $path, $publicurl);

                return redirect('zones/content/list/'.$zone->id);
	} else {
		//TODO error
		return redirect('zones/content/list/'.$zone->id);
	}
    }

    public function showZoneAddZone($id, Request $request) {
        try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

	$zones = Zone::getPermittedZones(Auth::user(), "sub");

        return view('zones.add.zone', array('title' => 'Add Sub Zone', 'zone' => $zone, 'zones' => $zones));
    }

    public function doZoneAddZone($id, Request $request) {
	try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

	$optionid = $request->input('zone');
	try {
		$subzone = Zone::findOrFail($optionid);
	} catch (Exception $ex) {
		//TODO error
		return redirect('zones/content/list/'.$zone->id);
	}

	if (!$subzone->hasPermission(Auth::user()->id)) {
		//TODO error
		return redirect('zones/content/list/'.$zone->id);
	}

	$zone->addContent(Auth::user(), "zone", $subzone->id, $subzone->name);
	return redirect('zones/content/list/'.$zone->id);
    }

    public function showZoneAddVideo($id, Request $request) {
        try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

        return view('zones.add.video', array('title' => 'Add Video', 'zone' => $zone));
    }

    public function doZoneAddVideo($id, Request $request) {
        try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        } 

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

        if ($request->hasFile('file')) {
                $path = $request->file('file')->storePublicly('public');
                $publicurl = url(Storage::url($path));

                $zone->addContent(Auth::user(), "video", $path, $publicurl);

                return redirect('zones/content/list/'.$zone->id);
        } else {
                //TODO error
                return redirect('zones/content/list/'.$zone->id);
        }
    }

    public function showZoneAddPDF($id, Request $request) {
	try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

	return view('zones.add.pdf', array('title' => 'Add PDF', 'zone' => $zone));
    }

    public function doZoneAddPDF($id, Request $request) {
	try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

	if ($request->hasFile('file')) {
		$path = $request->file('file')->storePublicly('public');
		$publicurl = url(Storage::url($path));
                $path = "/var/www/html/storage/app/".$path;

		$job = QueuedJob::createNew();
                $job->redirect_to = url('zones/content/list/'.$zone->id);
                $job->save();

                ConvertPDF::dispatch(Auth::user(), $zone, $path, $job);

                return redirect('job/'.$job->id);
	} else {
		//TODO error
		return redirect('zones/content/list/'.$zone->id);
	}
    }

    public function showZoneAddSlides($id, Request $request) {
        try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

	$token = GoogleToken::getForUser(Auth::user()->id);
	if (!$token->isCorrectlyLinked()) {
		//TODO error
		return redirect('account');
	}

        return view('zones.add.slides', array('title' => 'Import Slides', 'zone' => $zone));
    }

    public function doZoneAddSlides($id, Request $request) {
        try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        } 

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        } 

	$token = GoogleToken::getForUser(Auth::user()->id);
        if (!$token->isCorrectlyLinked()) {
                //TODO error
                return redirect('account');
        }
	$client = $token->getAPIObject();

	$presentation = $request->input('presentation');

	//validate url
	if(strpos($presentation, "https://docs.google.com/presentation/d/") !== false && strpos($presentation, "/edit") !== false) {
		//good url
		$step1 = explode("presentation/d/", $presentation)[1];
		$step2 = explode("/edit", $step1)[0];
		$presentation_id = $step2;
	} else {
		//bad url
		//TODO error
                return redirect()->back()->withErrors(['menuid' => 'The provided URL is not valid.']);
	}

	$job = QueuedJob::createNew();
	$job->redirect_to = url('zones/content/list/'.$zone->id);
	$job->save();

	ConvertGoogleSlides::dispatch(Auth::user(), $zone, $presentation_id, $job);

	return redirect('job/'.$job->id);
    }

    public function showZoneAddWeather($id, Request $request) {
        try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

        if (!config('digitalsignage.enable_weather')) {
                //TODO error
                return redirect('zones');
        }

        return view('zones.add.weather', array('title' => 'Import NWS Weather', 'zone' => $zone));
    }

    public function doZoneAddWeather($id, Request $request) {
        try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        } 

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

        if (!config('digitalsignage.enable_weather')) {
                //TODO error
                return redirect('zones');
        }

	$stationcode = e($request->input('station'));
        $stationcode = preg_replace("/[^a-zA-Z0-9]/", "", $stationcode);

        if ($stationcode == "") {
                //stationcode is blank
                return redirect()->back()->withErrors(['menuid' => 'Station cannot be blank.']);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://api.weather.gov/stations/".$stationcode."/observations/latest");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "DigitalSignage/1.0");
        $response = curl_exec($ch);
        $statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statuscode != 200) {
                //station code is invalid
                return redirect()->back()->withErrors(['menuid' => 'The provided ID is not valid.']);
        }

	$job = QueuedJob::createNew();
	$job->redirect_to = url('zones/content/list/'.$zone->id);
	$job->save();

        UpdateWeatherImage::dispatch(Auth::user(), $zone, $stationcode, $job);

	return redirect('job/'.$job->id);
    }

    public function showZoneAddText($id, Request $request) {
        try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

        if (!config('digitalsignage.enable_alertgen')) {
                //TODO error
                return redirect('zones');
        }

        return view('zones.add.text', array('title' => 'Add Text', 'zone' => $zone));
    }

    public function doZoneAddText($id, Request $request) {
        try {
                $zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
                //TODO error
                return redirect('zones');
        } 

        if (!$zone->hasPermission(Auth::user()->id)) {
                //TODO error
                return redirect('zones');
        }

        if (!config('digitalsignage.enable_alertgen')) {
                //TODO error
                return redirect('zones');
        }

	$text = $request->input('text');

	$job = QueuedJob::createNew();
	$job->redirect_to = url('zones/content/list/'.$zone->id);
	$job->save();

	FetchInformationText::dispatch(Auth::user(), $zone, $text, $job);

	return redirect('job/'.$job->id);
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
