<?php

namespace App\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use Auth;

class SAMLLoginListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Saml2LoginEvent  $event
     * @return void
     */
    public function handle(Saml2LoginEvent $event)
    {
        $messageId = $event->getSaml2Auth()->getLastMessageId();
        // TODO:Add your own code preventing reuse of a $messageId to stop replay attacks

    	$user = $event->getSaml2User();
    	$userData = [
        	'id' => $user->getUserId(),
        	'attributes' => $user->getAttributes(),
        	'assertion' => $user->getRawSamlAssertion()
    	];

	$mail = false;
	try {
		$mail = $userData['attributes']['mail'][0];
		if (!isset($mail) || $mail == false) {
			throw new Exception;
		}
	} catch (Exception $ex) {
		return;
	}

        $laravelUser = User::resolveUser($mail);
        Auth::login($laravelUser);
    }
}
