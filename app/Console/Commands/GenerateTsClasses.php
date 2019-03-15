<?php namespace App\Console\Commands;

use Schema;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class GenerateTsClasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ts:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate typescript class files for all laravel models.';

    /**
     * Laravel Filesystem instance.
     *
     * @var Filesystem
     */
    protected $fs;

    protected $availableRelations = [
        'hasMany',
        'hasManyThrough',
        'belongsToMany',
        'hasOne',
        'belongsTo',
        'morphOne',
        'morphTo',
        'morphMany',
        'morphToMany'
    ];

    const MYSQL_TO_TS_TYPE_MAP = [
        'integer' => 'number',
        'text' => 'string',
        'string' => 'string',
        'boolean' => 'boolean',
        'datetime' => 'string',
        'smallint' => 'number'
    ];

    const PROPS_TO_SKIP = [
        'Illuminate\Notifications\DatabaseNotification'
    ];

    /**
     * GenerateTypescriptDefinitionsForModels constructor.
     *
     * @param Filesystem $fs
     */
    public function __construct(Filesystem $fs)
    {
        parent::__construct();

        $this->fs = $fs;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $fileNames = collect($this->fs->files(base_path('app')));

        $models = $fileNames->map(function($path) {
            return $this->makeModelFromFilename($path);
        })->filter();

        $models = $models->mapWithKeys(function($model) {
            $key = class_basename($model);
            $value = array_merge(
                $this->generatePropertiesTypes($model),
                $this->generateRelationsTypes($model)
            );
            return [$key => $value];
        });

        $interfaces = $this->generateTsClasses($models);

        $this->saveClassesOnDisk($interfaces);

        $this->info('Typescript definitions files generated.');
    }

    /**
     * Save generated typescript classes to filesystem.
     *
     * @param $classes string[]
     */
    private function saveClassesOnDisk($classes)
    {
        $path = base_path('../client/src/app/models');

        $this->fs->cleanDirectory($path);

        foreach($classes as $name => $content) {
            $this->fs->put("$path/$name.ts", $content);
        }
    }

    /**
     * Generate typescript classes for specified models.
     *
     * @param array $data
     * @return string[]
     */
    private function generateTsClasses($data)
    {
        $classes = [];

        foreach($data as $name => $props) {
            $class = $this->generateTsImports($props, $name);

            $class .= "export class $name {\n";

            foreach ($props as $prop) {
                $class .= $this->generateTsPropertyLine($prop);
            }

            //add constructor
            $class .= $this->generateTsConstructor();

            $class .= "\n}";

            $classes[$name] = trim($class);
        }

        return $classes;
    }

    /**
     * Generate needed import lines (for relations) for TS class.
     *
     * @param array  $props
     * @param string $className
     * @return string
     */
    private function generateTsImports($props, $className)
    {
        $imports = '';

        foreach ($props as $prop) {
            if (array_search($prop['name'], self::PROPS_TO_SKIP) > -1 || $prop['name'] === $className) continue;

            if ($prop['type'] === 'relation') {
                $line = "import {{$prop['name']}} from \"./{$prop['name']}\";\n";

                if ( ! str_contains($imports, $line)) {
                    $imports .= $line;
                }
            }
        }

        //add extra new line if there we any imports
        return $imports ? $imports."\n" : '';
    }

    /**
     * Generate constructor string for TS class.
     *
     * @return string
     */
    private function generateTsConstructor()
    {
        return "\n\tconstructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }";
    }

    /**
     * Generate TS class property line.
     * "propertyName?: type"
     *
     * @param array $config
     * @return string
     */
    private function generateTsPropertyLine($config)
    {
        if (array_search($config['name'], self::PROPS_TO_SKIP) > -1) return '';

        if ($config['type'] === 'relation') {
            //add [] if it's a many to many or many to one relationship
            $multi = $config['multi'] ? '[]' : '';
            $line = "{$config['method']}?: {$config['name']}$multi";
        } else {
            //add ? if property is optional
            $optional = $config['required'] ? '' : '?';
            $line =  "{$config['name']}$optional: {$config['type']}";
        }

        //cast default value to "string boolean" if needed
        if ($config['default'] && $config['type'] === 'boolean') {
            $default = " = true";
        } else {
            $default = $config['default'] ? " = '{$config['default']}'" : '';
        }

        //tab, propertyName: propertyType, new line
        return "\t" . $line .= "$default;" . "\n";
    }

    /**
     * Generate types for all model properties (propName => type)
     *
     * @param Model $model
     * @return array
     */
    private function generatePropertiesTypes(Model $model)
    {
        $appends = collect($model->toArray())->map(function($value, $key) {
            return [
                'name'     => $key,
                'type'     => 'string',
                'required' => true,
                'default'  => null,
            ];
        });

        $columns = collect(Schema::getColumnListing($model->getTable()));

        $columns = $columns->map(function($columnName) use($model) {
            $column = Schema::getConnection()->getDoctrineColumn($model->getTable(), $columnName);

            return [
                'name'     => $columnName,
                'type'     => self::MYSQL_TO_TS_TYPE_MAP[$column->getType()->getName()],
                'required' => $column->getNotnull(),
                'default'  => $column->getDefault(),
            ];
        });

        return $columns->merge($appends)->toArray();
    }

    /**
     * Generate types for all model relationships (relName => RelatedModelName)
     *
     * @param Model $model
     * @return array
     */
    public function generateRelationsTypes(Model $model) {

        $relationsTypes = [];

        $methods = get_class_methods($model);

        foreach($methods as $method) {
            if ( ! method_exists('Illuminate\Database\Eloquent\Model', $method) && ! Str::startsWith($method, 'get')) {
                $reflection = new \ReflectionMethod($model, $method);

                $file = new \SplFileObject($reflection->getFileName());
                $file->seek($reflection->getStartLine() - 1);

                $code = '';
                while ($file->key() < $reflection->getEndLine()) {
                    $code .= $file->current();
                    $file->next();
                }
                $code = trim(preg_replace('/\s\s+/', '', $code));
                $begin = strpos($code, 'function(');
                $code = substr($code, $begin, strrpos($code, '}') - $begin + 1);

                foreach ($this->availableRelations as $relation) {
                    $search = '$this->' . $relation . '(';
                    if ($pos = stripos($code, $search)) {
                        $relationObj = $model->$method();

                        if ($relationObj instanceof Relation) {
                            $relatedModel = str_replace('App\\', '', get_class($relationObj->getRelated()));
                            $ManyToManyRels = ['hasManyThrough', 'belongsToMany', 'hasMany', 'morphMany', 'morphToMany'];

                            $relationsTypes[] = [
                                'type'    => 'relation',
                                'name'    => $relatedModel,
                                'method'  => $method,
                                'default' => null,
                                'multi'   => in_array($relation, $ManyToManyRels),
                            ];
                        }
                    }
                }
            }
        }

        return $relationsTypes;
    }

    /**
     * Make and return model class from given file name.
     *
     * @param string $name
     * @return mixed|null
     */
    private function makeModelFromFilename($name)
    {
        try {
            $model = $this->laravel->make('App\\'.basename($name, '.php'));
        } catch(Exception $e) {
            $model = null;
        }

        return $model;
    }
}
