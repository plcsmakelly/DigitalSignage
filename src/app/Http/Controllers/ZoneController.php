<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Zone;
use App\Models\ZonePermission;
use App\Models\ZoneContent;
use App\Models\User;
use Auth;
use Exception;

class ZoneController extends Controller
{
    public function showZoneList(Request $request) {
		$zones = Zone::getPermittedZones(Auth::user());

		return view('zones.list', array('title' => 'Zones', 'zones' => $zones));
		}

		public function showNewZone(Request $request) {
		if (!Auth::user()->isAdmin()) {
			return redirect('zones');
		}
		return view('zones.new', array('title' => 'New Zone'));
    }

    public function doNewZone(Request $request) {
		$name = e($request->input('name'));
		$type = $request->input('type');

		if (strlen($name) == 0) {
			//must specify name
			return redirect()->back();
		}

		if (!Auth::user()->isAdmin()) {
			return redirect('zones');
        }

		$zone = new Zone;
		if ($type == "Root Zone") {
			$zone->type = "root_zone";
		} else if ($type == "Sub Zone") {
			$zone->type = "sub_zone";
		} else {
			$zone->type = "root_zone";
		}

		$zone->name = $name;
		$zone->save();

		$permission = new ZonePermission;
		$permission->zone_id = $zone->id;
		$permission->user_id = Auth::user()->id;
		$permission->save();

		return redirect('zones');
   }

   public function showZonePermissions($id, Request $request) {
		try {
			$zone = Zone::findOrFail($id);
		} catch (Exception $ex) {
			//TODO error
			return redirect('zones');
		}

		if (!$zone->hasPermission(Auth::user()->id)) {
			//TODO error
			return redirect('zones');
		}

		return view('zones.permissions', array('title' => 'Edit Zone', 'zone' => $zone));
   }

   public function doAddZonePermission($id, Request $request) {
		try {
			$zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
			//TODO error
			return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
			//TODO error
			return redirect('zones');
        }

		$email = e($request->input('email'));
		if (strlen($email) < 5) {
			return redirect('zones/permissions/list/'.$id);
		}

		$permission = new ZonePermission;
		$permission->zone_id = $id;
		$permission->user_id = User::resolveUser($email)->id;
		$permission->save();

		return redirect('zones/permissions/list/'.$id);
   }

   public function doRemoveZonePermission($id, Request $request) {
		try {
			$permission = ZonePermission::findOrFail($id);
		} catch (Exception $ex) {
			//TODO error
			return redirect('zones');
		}

		$zone = $permission->getZone();
		if (!$zone->hasPermission(Auth::user()->id)) {
			//TODO error
			return redirect('zones');
		}

		if ($permission->user_id == Auth::user()->id) {
			return redirect('zones/permissions/list/'.$zone->id);
		} else {
			$content = ZoneContent::where('zone_id', $zone->id)->where('uploaded_by', $permission->user_id)->where('media_type', 'googleslides')->get();
			foreach($content as $item) {
				$zone->deleteContent($item);
			}

			$permission->delete();
			return redirect('zones/permissions/list/'.$zone->id);
		}
   }

   public function showZoneContent($id, Request $request) {
		try {
			$zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
			//TODO error
			return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
			//TODO error
			return redirect('zones');
        }

		return view('zones.content', array('title' => 'Zone Content', 'zone' => $zone));
   }

   public function doDeleteZoneContent($id, Request $request) {
		try {
			$content = ZoneContent::findOrFail($id);
		} catch (Exception $ex) {
			//TODO error
			return redirect('zones');
		}

		$zone = $content->getZone();
		if (!$zone->hasPermission(Auth::user()->id)) {
			//TODO error
			return redirect('zones');
		}

		$zone->deleteContent($content);
		return redirect('zones/content/list/'.$zone->id);
   }

   public function doDeleteZone($id, Request $request) {
		try {
			$zone = Zone::findOrFail($id);
        } catch (Exception $ex) {
			//TODO error
			return redirect('zones');
        }

        if (!$zone->hasPermission(Auth::user()->id)) {
			//TODO error
			return redirect('zones');
        }

		if (!Auth::user()->isAdmin()) {
			//TODO error
			return redirect('zones');
		}

		//delete all content
		foreach($zone->getContent() as $content) {
			$zone->deleteContent($content);
        }

		//delete all sub zone assignments
		$subzones = ZoneContent::where('media_type', 'zone')->where('original_url', $zone->id)->get();
		foreach($subzones as $subzone) {
			$masterzone = $subzone->getZone();
			$masterzone->deleteContent($subzone);
		}

		//get all devices
		$devices = $zone->getDevices();
		foreach($devices as $device) {
			$device->zone_id = 0;
			$device->save();
		}

		$zone->delete();

		return redirect('zones');
   }

   public function doDeleteAllZoneContent($id, Request $request) {
		try {
			$zone = Zone::findOrFail($id);
		} catch (Exception $ex) {
			//TODO error
			return redirect('zones');
		}

		if (!$zone->hasPermission(Auth::user()->id)) {
			//TODO error
			return redirect('zones');
		}

		foreach($zone->getContent() as $content) {
			$zone->deleteContent($content);
		}

		return redirect('zones/content/list/'.$zone->id);
   }

   public function doRefreshZoneContent($id, Request $request) {
		try {
			$content = ZoneContent::findOrFail($id);
		} catch (Exception $ex) {
			//TODO error
			return redirect('zones');
		}

		$zone = $content->getZone();
		if (!$zone->hasPermission(Auth::user()->id)) {
			//TODO error
			return redirect('zones');
		}

		if ($content->last_dynamic_refresh != null) {
			$lastRefresh = new \Carbon\Carbon($content->last_dynamic_refresh);
			if (!$lastRefresh->addMinutes(1)->isPast()) {
				//don't allow a refresh if last refresh less than 1 minute ago
				return redirect('zones/content/list/'.$zone->id);
			}
		}

		$content->triggerDynamicRefresh();

		return redirect('zones/content/list/'.$zone->id.'#content'.$content->id);
	}

   public function doToggleZoneContent($id, Request $request) {
		try {
            $content = ZoneContent::findOrFail($id);
        } catch (Exception $ex) {
            //TODO error
            return redirect('zones');
        }

        $zone = $content->getZone();
        if (!$zone->hasPermission(Auth::user()->id)) {
            //TODO error
             return redirect('zones');
        }

		if ($content->media_hidden) {
			$content->media_hidden = false;
		} else {
			$content->media_hidden = true;
		}
		$content->save();

		return redirect('zones/content/list/'.$zone->id.'#content'.$content->id);
   }

   public function doMoveUpZoneContent($id, Request $request) {
		try {
			$content = ZoneContent::findOrFail($id);
		} catch (Exception $ex) {
			//TODO error
			return redirect('zones');
		}

		$zone = $content->getZone();
		if (!$zone->hasPermission(Auth::user()->id)) {
			//TODO error
			return redirect('zones');
		}

		if ($content->order != 1) {
			$zone->moveContent($content, ($content->order - 1));
		}

		return redirect('zones/content/list/'.$zone->id.'#content'.$content->id);
   }

   public function doMoveDownZoneContent($id, Request $request) {
        try {
			$content = ZoneContent::findOrFail($id);
        } catch (Exception $ex) {
			//TODO error
			return redirect('zones');
        }

        $zone = $content->getZone();
        if (!$zone->hasPermission(Auth::user()->id)) {
			//TODO error
			return redirect('zones');
        }
		$count = count($zone->getContent());

        if ($content->order != $count) {
			$zone->moveContent($content, ($content->order + 1));
        }

        return redirect('zones/content/list/'.$zone->id.'#content'.$content->id);
   }
}
