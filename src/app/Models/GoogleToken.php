<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Google_Client;
use Google_Service_Slides;
use Google_Service_Drive;
use Exception;

class GoogleToken extends Model
{
    use HasFactory;

    public static function getForUser($userid) {
		$user = User::findOrFail($userid);

		try {
			$token = GoogleToken::where('user_id', $userid)->firstOrFail();
		} catch (Exception $ex) {
			$token = new GoogleToken;
			$token->user_id = $userid;
			$token->google_email = "not signed in";
			$token->refresh_token = "";
			$token->save();
		}

		return $token;
    }

    public function getAPIObject() {
		$user = $this->getUser();

		$client = new Google_Client();
        $client->setApplicationName(config('google.app_name'));
        $client->setScopes(Google_Service_Slides::PRESENTATIONS_READONLY);
        $client->addScope(Google_Service_Drive::DRIVE_READONLY);
		$client->addScope('https://www.googleapis.com/auth/userinfo.email');
        $client->setIncludeGrantedScopes(true);
        $client->setAuthConfig('/var/www/html/google.json');
        $client->setAccessType('offline');
        $client->setRedirectUri(url('google/oauth_callback'));
        $client->setLoginHint($user->email);
        $client->setPrompt('consent');

		if ($this->refresh_token != "") {
			$accessToken = json_decode($this->refresh_token, true);
			$client->setAccessToken($accessToken);
		}

		return $client;
    }

    public function storeRefreshToken($token) {
		$this->refresh_token = $token;
		$this->save();
    }

    public function isCorrectlyLinked() {
		if ($this->google_email == "not signed in") {
			return false;
		}

		$client = $this->getAPIObject();

		if ($client->isAccessTokenExpired() && $client->getRefreshToken()) {
			$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			if (!$client->isAccessTokenExpired()) {
				$this->storeRefreshToken(json_encode($client->getAccessToken()));
			}
		}

		return !($client->isAccessTokenExpired());
    }

    public function getUser() {
		return User::findOrFail($this->user_id);
    }
}
