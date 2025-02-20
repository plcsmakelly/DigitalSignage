<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QueuedJob;
use Exception;

class JobController extends Controller
{
    public function showJobStatus($id, Request $request) {
		try {
			$job = QueuedJob::findOrFail($id);
		} catch (Exception $ex) {
			return view('job.finished', array('title' => 'Job Finished'));
		}

		if ($job->finished) {
			return redirect($job->redirect_to);
		} else {
			return view('job.processing', array('title' => 'Job Processing', 'job' => $job));
		}
    }
}
