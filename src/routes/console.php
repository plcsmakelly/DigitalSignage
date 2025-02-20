<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('make-admin {user}', function($user) {
	$user = User::where('email', $user)->first();
	if (!$user) {
		$this->warn('Could not find user with the specified email address');
		return;
	}
	$user->role = "admin";
	$user->save();
	$this->info('Successfully changed user to admin.');
})->purpose('Change a standard user to be an admin user');

Artisan::command('make-user {user}', function($user) {
	$user = User::where('email', $user)->first();
	if (!$user) {
		$this->warn('Could not find user with the specified email address');
		return;
	}
	$user->role = "user";
	$user->save();
	$this->info('Successfully changed user to user.');
})->purpose('Change an admin user to be a standard user');