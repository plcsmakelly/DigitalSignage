<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Slides;
//use Google_Service_Drive;
use Exception;

class GoogleController extends Controller
{
    private function getClientObject() {
		$client = new Google_Client();
        $client->setApplicationName(config('google.app_name'));
        $client->setScopes(Google_Service_Slides::PRESENTATIONS_READONLY);
		// TODO Read other types of files from GDrive
		//$client->addScope(Google_Service_Drive::DRIVE_READONLY);
		$client->setAuthConfig('/var/www/html/google.json');
        $client->setAccessType('offline');
		$client->setRedirectUri(url('google/oauth_callback'));
        $client->setPrompt('consent');

        $tokenPath = "/var/www/html/storage/token.json";
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

		return $client;
    }

    public function activateClient(Request $request) {
		$client = $this->getClientObject();

		if ($client->isAccessTokenExpired()) {
			//no token or token is expired
			
			if ($client->getRefreshToken()) {
				//if we have a refresh token, use that
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
				echo "Already activated.";
			} else {
				//request new auth
				$authUrl = $client->createAuthUrl();
				echo "Go to".$authUrl;
	//			return redirect($authUrl);
			}
		}
   }

   public function submitToken(Request $request) {
		$client = $this->getClientObject();
		$tokenPath = "/var/www/html/storage/token.json";
		$authCode = $request->input('code');

		if ($client->isAccessTokenExpired()) {
			//no token or token is expired
			if ($client->getRefreshToken()) {
				//if we have a refresh token, use that
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
				echo "Already activated.";
			} else {
				$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
				$client->setAccessToken($accessToken);

				// Check to see if there was an error.
				if (array_key_exists('error', $accessToken)) {
					throw new Exception(join(', ', $accessToken));
				}
			}
			if (!file_exists(dirname($tokenPath))) {
				mkdir(dirname($tokenPath), 0700, true);
			}
			echo "Stored.";
			file_put_contents($tokenPath, json_encode($client->getAccessToken()));
		}
   }
}
