<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonePermission extends Model
{
    use HasFactory;

    public function getUser() {
	    return User::findOrFail($this->user_id);
    }

    public function getZone() {
	    return Zone::findOrFail($this->zone_id);
    }
}
