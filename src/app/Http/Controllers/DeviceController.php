<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Zone;
use Auth;
use Exception;

class DeviceController extends Controller
{
    public function getDeviceCSV($device, Request $request) {
		$device = str_replace(".csv", "", $device);

		$dev = Device::resolveDevice($device);
		$dev->markSeen();
		$zone = $dev->getZone();

		$urls = array();

		if (!$zone) {
			//no zone is assigned
		} else {
			$content = $zone->getContentCollapsed();
			foreach($content as $item) {
				if (!$item->fetched) {
					$item->fetched = true;
					$item->save();
				}
				if (!$item->shouldBeHidden()) {
					$contenturls = $item->getMediaURLs();
					foreach($contenturls as $contenturl) {
						$urls[] = $contenturl;
					}
				}
			}
		}

		if (count($urls) == 0) {
			$urls[] = url("no_content_1.png");
			$urls[] = url("no_content_2.png");
		}

		//add a second entry of the same content to work around Exhibit bug (after playing video, single content fails to load)
		//if (count($urls) == 1) {
		//	$urls[] = $urls[0]."?1";
		//}

		return response(view('device.csv', array('urls' => $urls)))->header('Content-Type', 'text/plain');
    }

    public function showDeviceList(Request $request) {
		if (!Auth::user()->isAdmin()) {
			return redirect('zones');
		}

		$devices = Device::all();
		return view('devices.list', array('title' => 'Devices', 'devices' => $devices));
    }

    public function showEditDevice($id, Request $request) {
		if (!Auth::user()->isAdmin()) {
					return redirect('zones');
			}

		try {
			$device = Device::findOrFail($id);
		} catch (Exception $ex) {
			return redirect('devices');
		}

		$zones = Zone::getPermittedZones(Auth::user(), "root");
		return view('devices.edit', array('title' => 'Assign Device', 'device' => $device, 'zones' => $zones));
    }

    public function doEditDevice($id, Request $request) {
		if (!Auth::user()->isAdmin()) {
                return redirect('zones');
        }

        try {
                $device = Device::findOrFail($id);
        } catch (Exception $ex) {
                return redirect('devices');
        }

		$zoneid = $request->input('zone');

		if ($zoneid != "0") {
			try {
				$zone = Zone::findOrFail($zoneid);
			} catch (Exception $ex) {
				return redirect('devices/edit/'.$device->id);
			}

			if (!$zone->hasPermission(Auth::user()->id)) {
				return redirect('devices/edit/'.$device->id);
			}

			$device->zone_id = $zone->id;
			$device->save();
		} else {
			$device->zone_id = 0;
			$device->save();
		}

		return redirect('devices');
    }

	public function doDeleteDevice($id, Request $request) {
		if (!Auth::user()->isAdmin()) {
			return redirect('zones');
		}

		try {
			$device = Device::findOrFail($id);
		} catch (Exception $ex) {
			return redirect('devices');
		}

		$device->delete();

		return redirect('devices');
	}
}
