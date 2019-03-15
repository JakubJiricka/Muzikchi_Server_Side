<?php

use App\Localization;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\LocalizationsController;

class LocalizationsTableSeeder extends Seeder
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Localization
     */
    private $localization;

    /**
     * LocalizationsTableSeeder constructor.
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
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paths = LocalizationsController::DEFAULT_TRANS_PATHS;

        $combined = [];

        //create source => localization json object from default localization files
        foreach ($paths as $path) {
            if ( ! $this->fs->exists(resource_path($path))) continue;
            $combined = array_merge($combined, json_decode($this->fs->get(resource_path($path)), true));
        }

        $localizations = $this->localization->get();

        if ($localizations->isNotEmpty()) {
            $this->mergeExistingTranslationLines($localizations, $combined);
        } else {
            $this->createNewLocalization('English', $combined);
        }
    }

    /**
     * Merge existing localization translation lines with specified ones.
     *
     * @param Collection $localizations
     * @param array $lines
     */
    private function mergeExistingTranslationLines($localizations, $lines)
    {
        $localizations->each(function (Localization $localization) use($lines) {
            $localization->lines = json_encode(array_merge($lines, $localization->lines));
            $localization->save();
        });
    }

    /**
     * Create new localization with specified name and translation lines.
     *
     * @param string $name
     * @param array $combined
     */
    private function createNewLocalization($name, $combined)
    {
        $this->localization->create([
            'name' => $name,
            'lines' => json_encode($combined)
        ]);
    }
}
