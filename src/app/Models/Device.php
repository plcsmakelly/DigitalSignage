<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;

class Device extends Model
{
    use HasFactory;

    public function getZone() {
		try {
			return Zone::findOrFail($this->zone_id);
		} catch (Exception $ex) {
			return false;
		}
    }

    public static function resolveDevice($devicename) {
		try {
			$device = Device::where('name', $devicename)->firstOrFail();
		} catch (Exception $ex) {
			$device = new Device;
			$device->name = $devicename;
			$device->zone_id = 0;
			$device->last_check = Carbon::now();
            $device->save();
		}
		return $device;
    }

    public function markSeen() {
		$this->last_check = Carbon::now();
		$this->save();
    }

	public function hasBeenSeenRecently() {
		return \Carbon\Carbon::now()->diffInSeconds($this->last_check) < (5*60);
	}
}
