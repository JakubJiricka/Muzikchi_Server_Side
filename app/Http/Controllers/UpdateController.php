<?php namespace App\Http\Controllers;

use DB;
use Auth;
use Cache;
use Artisan;
use Exception;
use Vebto\Settings\Setting;
use Vebto\Bootstrap\Controller;
use Vebto\Settings\DotEnvEditor;

class UpdateController extends Controller {
    /**
     * @var DotEnvEditor
     */
    private $dotEnvEditor;

    /**
     * @var Setting
     */
    private $setting;

    /**
     * UpdateController constructor.
     *
     * @param DotEnvEditor $dotEnvEditor
     * @param Setting $setting
     */
	public function __construct(DotEnvEditor $dotEnvEditor, Setting $setting)
	{
	    if ( ! config('vebto.site.disable_update_auth')) {
            if ( ! Auth::check() || ! Auth::user()->hasPermission('admin')) {
                abort(403);
            }
        }

        $this->setting = $setting;
        $this->dotEnvEditor = $dotEnvEditor;
    }

    /**
     * Show update view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        return view('update');
    }

    /**
     * Perform the update.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update()
	{
        //fix "index is too long" issue on MariaDB and older mysql versions
        \Schema::defaultStringLength(191);

        Artisan::call('migrate', ['--force' => 'true']);
        Artisan::call('db:seed', ['--force' => 'true']);
        Artisan::call('vebto:seed');

        //set albums table collation and charset to utf8mb4
        try {
            $tables = DB::select('SHOW TABLES');
            $prefix = DB::getTablePrefix();

            foreach($tables as $table) {
                $name = head($table);
                DB::statement("ALTER TABLE {$prefix}{$name} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            }
        } catch(Exception $e) {
            //
        }

        //move translations from database to filesystem
        if (version_compare(config('vebto.site.version'), '2.2.0') === -1) {
            Artisan::call('translations:migrate_from_database');
            Artisan::call('custom_code:migrate_from_database');
            $this->setting->where('name', 'dates.format')->update(['value' => 'yyyy-MM-dd']);
        }

        //versions earlier then 2.1.8 were using symlinks by default
        if (version_compare(config('vebto.site.version'), '2.1.8') === -1) {
            $this->dotEnvEditor->write(['USE_SYMLINKS' => true]);
            Artisan::call('storage:link');
        }

        //radio provider should always be spotify
        $this->setting->where('name', 'radio_provider')->update(['value' => 'Spotify']);

        $version = $this->getAppVersion();
        $this->dotEnvEditor->write(['app_version' => $version]);

        Cache::flush();

        return redirect()->back()->with('status', 'Updated the site successfully.');
	}


    /**
     * Get new app version.
     *
     * @return string
     */
    private function getAppVersion()
    {
        try {
            return $this->dotEnvEditor->load(base_path('.env.example'))['app_version'];
        } catch (Exception $e) {
            return '2.2.4';
        }
    }
}