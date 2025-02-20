<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class Zone extends Model
{
    use HasFactory;

    public function getPermissions() {
		return ZonePermission::where('zone_id', $this->id)->get();
    }

    public function getDevices() {
		return Device::where('zone_id', $this->id)->get();
    }

	public function getDeviceCount() {
		return Device::where('zone_id', $this->id)->count();
	}

	public function getContentCount() {
		return ZoneContent::where('zone_id', $this->id)->count();
	}

    public function hasPermission($userid) {
		try {
			$user = User::findOrFail($userid);

			if ($user->isAdmin()) {
				return true;
			}
		} catch (Exception $ex) {
		}

		$permissions = $this->getPermissions();

		foreach($permissions as $permission) {
			if ($permission->user_id == $userid) {
				return true;
			}
		}

		return false;
    }

    public static function getPermittedZones($user, $type = "any") {
		if ($user->isAdmin()) {
			if ($type == "any") {
				return Zone::orderBy('name', 'asc')->get();
			} else if ($type == "root") {
				return Zone::where('type', 'root_zone')->orderBy('name', 'asc')->get();
			} else if ($type == "sub") {
				return Zone::where('type', 'sub_zone')->orderBy('name', 'asc')->get();
			}
		}

		$permissions = ZonePermission::where('user_id', $user->id)->get();

		$zones = array();
		foreach ($permissions as $permission) {
			try {
				$option = Zone::findOrFail($permission->zone_id);
				if ($type == "any") {
					$zones[] = $option;
				} else if ($type == "sub") {
					if ($option->type == "sub_zone") {
						$zones[] = $option;
					}
				} else if ($type == "root") {
					if ($option->type == "root_zone") {
						$zones[] = $option;
					}
				}
			} catch (Exception $ex) {
				//TODO error
			}
		}

		return $zones;
    }

    public function getContent() {
		$content = ZoneContent::where('zone_id', $this->id)->orderBy('order', 'asc')->get();
		return $content;
    }

    public function getContentCollapsed() {
		$content = $this->getContent();
		$collapsed = array();
		foreach($content as $item) {
			if ($item->media_type == "zone") {
				//zones are different
				try {
					$zonecontent = Zone::findOrFail($item->original_url)->getContentCollapsed();
					foreach ($zonecontent as $zoneitem) {
						$collapsed[] = $zoneitem;
					}
				} catch (Exception $ex) {
					//bad array
					//TODO error
				}
			} else {
				$collapsed[] = $item;
			}
		}

		return $collapsed;
    }

    public function addContent($user, $mediatype, $origurl, $uploadurl, $start = 0, $duration = -1) {
		$existing = count($this->getContent());
		$content = new ZoneContent;
		$content->order = $existing + 1;
		$content->zone_id = $this->id;
		$content->media_type = $mediatype;
		$content->original_url = $origurl;
		$content->upload_url = $uploadurl;
		$content->start_time = $start;
		$content->duration = $duration;
		$content->uploaded_by = $user->id;
		$content->save();
		return $content;
    }

    public function deleteContent($content) {
		$content->prepForDelete();
		$content->delete();
		$remaining = $this->getContent();

		$count = 1;
		foreach ($remaining as $remains) {
			$remains->order = $count;
			$remains->save();
			$count = $count + 1;
		}
    }

    public function moveContent($content, $neworder) {
		try {
			$existing = ZoneContent::where('zone_id', $this->id)->where('order', $neworder)->firstOrFail();
			$existing->order = $content->order;
			$existing->save();
		} catch (Exception $ex) { }

		$content->order = $neworder;
		$content->save();
    }

	public function hasActiveAlert() {
		return false;
	}
}
