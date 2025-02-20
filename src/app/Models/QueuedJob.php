<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueuedJob extends Model
{
    use HasFactory;

    public static function createNew() {
		$job = new QueuedJob();
		$job->finished = false;
		$job->save();
		return $job;
    }

    public function statusUpdate($progress, $message) {
		$this->percent = $progress;
		$this->message = $message;
		$this->save();
    }

    public function markDone() {
		$this->finished = true;
		$this->save();
    }

    public function markFailed() {
		$this->failed = true;
		$this->finished = true;
		$this->save();
    }

    public function isDone() {
		return $this->finished;
    }

    public function isFailed() {
		return $this->failed;
    }
}
