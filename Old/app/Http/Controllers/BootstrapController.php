<?php namespace App\Http\Controllers;

use App\Services\BootstrapData;

class BootstrapController extends Controller
{
    /**
     * Get data needed to bootstrap the application.
     *
     * @param BootstrapData $bootstrapData
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBootstrapData(BootstrapData $bootstrapData)
    {
        return $this->success(['data' => $bootstrapData->get()]);
    }
}
