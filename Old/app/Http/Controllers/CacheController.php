<?php namespace App\Http\Controllers;

use Artisan;

class CacheController extends Controller {

    /**
     * Clear all application cache.
     *
     * @return \Illuminate\Http\JsonResponse
     */
	public function clear()
	{
        $this->authorize('index', 'ReportPolicy');

	    Artisan::call('cache:clear');

        return $this->success();
	}
}
