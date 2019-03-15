<?php namespace App\Http\Controllers;

use App\Localization;
use App\Services\Settings;
use Illuminate\Http\Request;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class LocalizationsController extends Controller
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Request
     */
    private $request;

    /**
     * Path to files with default localization language lines.
     */
    const DEFAULT_TRANS_PATHS = [
        'client-translations.json',
        'server-translations.json',
    ];

    /**
     * @var Localization
     */
    private $localization;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * LocalizationsController constructor.
     *
     * @param Filesystem $fs
     * @param Request $request
     * @param Localization $localization
     * @param Settings $settings
     */
    public function __construct(Filesystem $fs, Request $request, Localization $localization, Settings $settings)
    {
        $this->fs = $fs;
        $this->request = $request;
        $this->settings = $settings;
        $this->localization = $localization;
    }

    /**
     * Return all user created localizations.
     *
     * @return Collection
     */
    public function index()
    {
        $this->authorize('index', Localization::class);

        return $this->localization->get();
    }

    /**
     * Get localization by specified name.
     *
     * @param string $name
     * @return Localization
     */
    public function show($name)
    {
        $this->authorize('show', Localization::class);

        return $this->localization->where('name', $name)->firstOrFail();
    }

    /**
     * Update specified localization.
     *
     * @param integer $id
     * @return bool
     */
    public function update($id)
    {
        $this->authorize('update', Localization::class);

        $this->validate($this->request, [
            'name'  => 'string|min:1',
            'lines' => 'array|min:1'
        ]);

        $data = $this->request->except(['_method']);
        $localization = $this->localization->findOrFail($id);

        if (isset($data['lines']) && $data['lines'] && ! empty($data['lines'])) {
            //merge old and new translation lines
            $data['lines'] = json_encode(array_merge($localization->lines, $data['lines']));
        } else {
            unset($data['lines']);
        }

        return $this->localization->where('id', $id)->update($data);
    }

    /**
     * Create a new localization
     *
     * @return Localization
     */
    public function store()
    {
        $this->authorize('store', Localization::class);

        $this->validate($this->request, [
            'name' => 'required|unique:localizations'
        ]);

        $combined = [];

        //create source => localization json object from default localization files
        foreach (self::DEFAULT_TRANS_PATHS as $path) {
            $combined = array_merge($combined, json_decode($this->fs->get(resource_path($path)), true));
        }

        return $this->localization->create([
            'name'  => $this->request->get('name'),
            'lines' => json_encode($combined)
        ]);
    }

    /**
     * Delete specified language.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->authorize('destroy', Localization::class);

        $this->localization->findOrFail($id)->delete();

        return $this->success();
    }
}
