<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Exception;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function resolveUser($email) {
        try {
            $user = User::where('email', $email)->firstOrFail();
        } catch (Exception $ex) {
            $user = new User;
            $user->username = $email;
            $user->email = $email;
            $user->email_verified_at = Carbon::now();
            $user->password = "saml";
            $user->save();
        }
        return $user;
    }

    public function isAdmin() {
	    return ($this->role == "admin");
    }
}
