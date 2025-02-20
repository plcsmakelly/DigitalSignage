<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GoogleToken;
use Google_Service_Oauth2;
use Auth;
use Exception;

class AccountController extends Controller
{
    public function showAccountSettings(Request $request) {
		$user = Auth::user();
		$token = GoogleToken::getForUser($user->id);

		return view('account', array('title' => 'Account Settings', 'user' => $user, 'token' => $token));
    }

    public function startGoogleLink(Request $request) {
		$googleObject = GoogleToken::getForUser(Auth::user()->id)->getAPIObject();
		// TODO multiple hosted domains support
		$googleObject->setHostedDomain(config('google.hosted_domain'));
		$googleObject->setLoginHint(Auth::user()->email);
		$url = $googleObject->createAuthUrl();
		return redirect($url);
    }

    public function finishGoogleLink(Request $request) {
		$code = $request->input('code');
		$error = $request->input('error');

		$token = GoogleToken::getForUser(Auth::user()->id);
		$client = $token->getAPIObject();

		if (strlen($code) > 0) {
			//we have a code
			try {
				$accessToken = $client->fetchAccessTokenWithAuthCode($code);
				$client->setAccessToken($accessToken);
			} catch (Exception $ex) {
				return "invalid token";
			}

			//get the user's email
			try {
				$objOAuthService = new Google_Service_Oauth2($client);
				$userData = $objOAuthService->userinfo->get();
				$email = $userData->email;
			} catch (Exception $ex) {
				return "failed to get email";
			}

			$token->google_email = $email;
			$token->storeRefreshToken(json_encode($client->getAccessToken()));
		} else {
			//TODO error
			return "error";
		}

		return redirect('account');
    }
}
