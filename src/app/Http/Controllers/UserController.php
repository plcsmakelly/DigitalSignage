<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aacotroneo\Saml2\Saml2Auth;

class UserController extends Controller
{
    public function startLogin(Request $request) {
	    $saml = new Saml2Auth(Saml2Auth::loadOneLoginAuthFromIpdConfig('myidp'));
	    return $saml->login('zones');
    }

    public function startLogout(Request $request) {
	    request()->session()->flush();
	    return redirect('saml/myidp/logout');
    }
}
