<?php namespace App\Http\Controllers;

use App;
use Illuminate\View\View;
use App\Services\Settings;
use App\Services\BootstrapData;

class HomeController extends Controller {

    /**
     * @var BootstrapData
     */
    private $bootstrapData;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * HomeController constructor.
     *
     * @param BootstrapData $bootstrapData
     * @param Settings $settings
     */
    public function __construct(BootstrapData $bootstrapData, Settings $settings)
    {
        $this->bootstrapData = $bootstrapData;
        $this->settings = $settings;
    }

    /**
	 * Show the application home screen to the user.
	 *
	 * @return View
	 */
	public function index()
	{
        $htmlBaseUri = '/';

        //get uri for html "base" tag
        if (substr_count(url(''), '/') > 2) {
            $htmlBaseUri = parse_url(url(''))['path'] . '/';
        }

        return view('main')
            ->with('bootstrapData', $this->bootstrapData->get())
            ->with('htmlBaseUri', $htmlBaseUri)
            ->with('settings', $this->settings);
	}
}
