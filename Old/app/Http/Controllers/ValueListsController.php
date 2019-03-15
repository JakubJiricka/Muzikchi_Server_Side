<?php namespace App\Http\Controllers;

use App\Localization;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class ValueListsController extends Controller
{
    /**
     * Laravel filesystem service instance.
     *
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Localization
     */
    private $localization;

    /**
     * ValueListsController constructor.
     *
     * @param Filesystem $fs
     * @param Localization $localization
     */
    public function __construct(Filesystem $fs, Localization $localization)
    {
        $this->fs = $fs;
        $this->localization = $localization;
    }

    /**
     * Get value list by specified name.
     *
     * @param string $name
     * @return mixed
     */
    public function getValueList($name)
    {
        $name = Str::studly($name);

        if ( ! method_exists($this, $name)) abort(404);

        return $this->$name();
    }

    /**
     * Get all available permissions.
     *
     * @return array
     */
    public function permissions()
    {
        $this->authorize('index', 'PermissionsPolicy');

        return $this->fs->getRequire(base_path('resources/value-lists/permissions.php'));
    }

    /**
     * Get timezones, countries and languages lists.
     *
     * @return array
     */
    public function selects()
    {
        $timezones = json_decode($this->fs->get(base_path('resources/value-lists/timezones.json')), true);
        $countries = json_decode($this->fs->get(base_path('resources/value-lists/countries.json')), true);
        $languages = $this->localization->get(['name'])->pluck('name')->toArray();

        return [
            'timezones' => array_values($timezones),
            'countries' => array_values($countries),
            'languages' => $languages,
        ];
    }
}
