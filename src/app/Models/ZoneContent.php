<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Jobs\UpdateLinqImage;
use App\Jobs\UpdateWeatherImage;
use Exception;

class ZoneContent extends Model
{
    use HasFactory;

    public function getMediaURLs() {
		$urls = array();

		if ($this->media_type == "zone") {
			$content = $this->getSubZone()->getContentCollapsed();
			foreach($content as $item) {
				if ($item->shouldBeHidden()) {
					continue;
				}
				foreach($item->getMediaURLs() as $subitem) {
					$urls[] = $subitem;
				}
			}
		} else {
			//other types return upload url
			$urls[] = $this->upload_url;
		}

		return $urls;
    }

    public function getSubZone() {
		if ($this->media_type == "zone") {
			return Zone::findOrFail($this->original_url);
		} else {
			return false;
		}
    }

    public function getZone() {
		return Zone::findOrFail($this->zone_id);
    }

	public function getUploadedByUser() {
		return User::find($this->uploaded_by);
	}

    public function getPreviewHTML() {
		switch($this->media_type) {
			case "picture":
				return '<a href="'.$this->upload_url.'" target="_blank"><img src="'.$this->upload_url.'" width="192" height="108" data-tippy-content="Click to preview in new tab" /></a>';
			break;
			case "video":
				return '<div><video width="192" height="108" style="display: inline;" controls><source src="'.$this->upload_url.'" type="video/mp4"></video> <a href="'.$this->upload_url.'" style="display: inline-block;" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" data-tippy-content="Click to preview in new tab" class="ml-1" height="1em" fill="currentColor" stroke="currentColor" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M320 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l82.7 0L201.4 265.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L448 109.3l0 82.7c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160c0-17.7-14.3-32-32-32L320 0zM80 32C35.8 32 0 67.8 0 112L0 432c0 44.2 35.8 80 80 80l320 0c44.2 0 80-35.8 80-80l0-112c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 112c0 8.8-7.2 16-16 16L80 448c-8.8 0-16-7.2-16-16l0-320c0-8.8 7.2-16 16-16l112 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 32z"/></svg></a></div>';
			break;
			case "zone":
				try {
					$parentzone = $this->getSubZone();
					$content = $parentzone->getContentCollapsed();
					if (count($content) > 0) {
						return $content[0]->getPreviewHTML();
					} else {
						return "Empty zone.";
					}
				} catch (Exception $ex) {
					return "Bad zone data.";
				}
			break;
		}
		return "No preview available.";
    }

	public function shouldBeHidden() {
		return $this->media_hidden;
	}

	public function getVisibilityStatus() {
		if ($this->getZone()->hasActiveAlert()) {
			return "hiddenbyalert";
		}

		if ($this->media_hidden) {
			return "hiddenbyrequest";
		}

		if ($this->fetched) {
			return "visible";
		} else {
			if ($this->media_type == "zone") {
				return "visible";
			}
			return "ready";
		}

		//visible, ready, hiddenbytime, hiddenbyalert, hiddenbyrequest
	}

    public function prepForDelete() {
		if ($this->media_type == "picture" || $this->media_type == "video") {
			Storage::delete($this->original_url);
        }
    }

	public function triggerDynamicRefresh() {
		if ($this->dynamic_type == "weather") {
			$job = QueuedJob::createNew();
        	UpdateWeatherImage::dispatch($this->getUploadedByUser(), $this->getZone(), $this->dynamic_source, $job, $this);
		} else if ($this->dynamic_type == "linq") {
			$job = QueuedJob::createNew();
        	UpdateLinqImage::dispatch($this->getUploadedByUser(), $this->getZone(), $this->dynamic_source, $job, $this);
		}
	}
}
