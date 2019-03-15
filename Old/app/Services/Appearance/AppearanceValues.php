<?php namespace App\Services\Appearance;

use App\Services\DotEnvEditor;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;

class AppearanceValues
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var FilesystemManager
     */
    private $storage;

    /**
     * Path to custom css theme.
     */
    const THEME_PATH = 'resources/value-lists/editable-theme.css';

    /**
     * Path to stored user selected values for css theme.
     */
    const THEME_VALUES_PATH = 'appearance/theme-values.json';

    /**
     * Path to default settings for the application.
     */
    const DEFAULT_SETTINGS_PATH = 'resources/value-lists/default-settings.php';

    /**
     * ENV values to include.
     */
    const ENV_KEYS = ['app_url', 'app_name'];

    /**
     * @var Client
     */
    private $http;

    /**
     * AppearanceManager constructor.
     *
     * @param Filesystem $fs
     * @param FilesystemManager $storage
     * @param Client $http
     */
    public function __construct(
        Filesystem $fs,
        FilesystemManager $storage,
        Client $http
    )
    {
        $this->fs = $fs;
        $this->storage = $storage;
        $this->http = $http;
    }

    /**
     * Get user defined and default values for appearance editor.
     *
     * @return array
     */
    public function get()
    {
        //get default settings for the application
        $settings = $this->fs->getRequire(base_path(self::DEFAULT_SETTINGS_PATH));

        list($theme, $variables) = $this->getCssThemeAndVariables();

        //merge default theme values with user selected values
        if ($this->storage->disk('public')->exists(self::THEME_VALUES_PATH)) {
            $variables = array_replace_recursive(
                $variables,
                json_decode($this->storage->disk('public')->get(self::THEME_VALUES_PATH), true)
            );
        }

        //add css theme to settings array
        $settings[] = ['name' => 'editable_theme', 'value' => $theme];

        //add routes
        $settings[] = ['name' => 'routes', 'value' => $this->getUserRoutes()];

        //add env settings
        $env = [];
        foreach (self::ENV_KEYS as $key) {
            $env['env.'.$key] = config(str_replace('_', '.', $key));
        }
        $settings[] = ['name' => 'env', 'value' => $env];

        return array_values(array_merge($settings, $variables));
    }

    /**
     * Get user facing routes for menu manager.
     *
     * @return array
     */
    private function getUserRoutes()
    {
        return $this->fs->getRequire(base_path('resources/value-lists/user-routes.php'));
    }

    /**
     * Get css theme and default variables for appearance editor.
     *
     * @return array
     */
    private function getCssThemeAndVariables()
    {
        $theme = $this->fs->get(base_path(self::THEME_PATH));

        //capture and remove css variables defined in :root
        preg_match('/:root {(.+?)}/s', $theme, $matches);
        $theme = trim(preg_replace('/:root {(.+?)}/s', '', $theme));

        //transform css variables into dot notation keys
        $theme = preg_replace_callback('/var\(--(.+?)\)/', function ($matches) {
            return str_replace('-', '.', $matches[1]);
        }, $theme);

        $lines = explode(PHP_EOL, trim($matches[1]));

        //transform css variables into key => value pairs
        $variables = array_map(function ($line) {
            $pair = explode(':', $line);
            $key = trim(str_replace(['--', '-'], ['', '.'], $pair[0]));
            $value = str_replace(';', '', trim($pair[1]));
            return ['name' => $key, 'value' => $value];
        }, $lines);

        return [$theme, $variables];
    }
}