<?php


namespace Kunsal\Laramodula\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Kunsal\Laramodula\Traits\ReplaceStubTrait;
use Illuminate\Support\Str;

class GenerateModule extends Command
{
    use ReplaceStubTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name} {--form=} {--migration} {--resource} {--type=} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates application module';

    /**
     * Instance of the filesystem
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $file;

    public function __construct(Filesystem $file)
    {
        parent::__construct();
        $this->file = $file;
    }

    /**
     * Execute the console command
     *
     * @return mixed
    */
    public function handle()
    {
        // Create Modules folder in laravel app directory
        if (!$this->file->exists('app/Modules')) {
            $this->file->makeDirectory('app/Modules');
        }
        $module = ucfirst($this->argument('name'));
        $migration = $this->option('migration');
        $resource = $this->option('resource');
        $type = $this->option('type');
        $form = $this->option('form');

        $module_plural = Str::plural($module);
        // Initial variables
        $module_path = 'app/Modules/'.$module_plural;
        $namespace = "App\\Modules\\{$module_plural}";

        if ($this->file->exists($module_path) && is_dir($module_path)) {
            $this->error($module . ' module already exists');
            exit;
        }
        if (!$this->file->exists($module_path.'/Core')) {
            $this->makeCoreModule();
        }
        $this->makeController($module, $module_path, $namespace, $resource);
        $this->makeModel($module, $namespace, $migration, $form);
        $this->makeInterface($module, $module_path, $namespace);
        $this->makeEloquentRepo($module, $module_path, $namespace);
        $this->makeProvider($module, $module_path, $namespace);
        $this->makeService($module, $module_path, $namespace);
        $this->makeEventProvider($module, $module_path, $namespace);

        $this->emptyFolder($module_path);


        $this->makeResources($module_path, $module);
    }

    // Get stub file
    protected function moduleStub($filename)
    {
        return __DIR__ . "/stubs/{$filename}.stub";
    }

    protected function makeController($module, $module_path, $namespace, $resource)
    {
        $controller_name = $module.'Controller';
        $this->call('make:controller', [
            'name' => $namespace."\\Http\\Controllers\\${controller_name}",
            '--resource' => $resource
        ]);

        file_put_contents("{$module_path}/Http/routes.php", ($resource == true) ? $this->resourceRoute($module) : $this->route($module));
        $this->makeRequests($module, $module_path, $namespace);
    }

    protected function makeModel($module, $namespace, $migration = false, $form = null)
    {
        $this->call('make:model', ['name' => $namespace."\\Models\\${module}"]);
        $module_plural = Str::plural($module);
        if ($migration) {
            $migration_path = "app/Modules/${module_plural}/Models/Migrations";
            $this->file->makeDirectory($migration_path);
            $this->call('make:migration', [
                'name' => 'create_'.strtolower($module_plural).'_table',
                '--create' => strtolower($module_plural),
                '--path' => $migration_path
            ]);
        }

        if (!is_null($form)) {
            $this->call('make:form', ['name' => $namespace.'\\Forms\\'.$module.'Form', '--fields' => $form]);
        } else {
            $this->call('make:form', ['name' => $namespace.'\\Forms\\'.$module.'Form']);
        }
    }

    protected function makeService($module, $module_path, $namespace)
    {
        // The stub file
        $stub_path = $this->moduleStub('service');

        // Data to replace in stub
        $plural = Str::plural($module);
        $lowerplural = strtolower($plural);
        $name = ucfirst($module);
        $lowername = strtolower($name);
        $class =  "Create".$name."Service";

        // Path to directory to create file in
        $service_path = "{$module_path}/Http/Services";
        // Create directory with read, write, execute
        mkdir($service_path, 0777, true);
        $stub = $this->file->get($stub_path);
        $stub = $this->replaceClass($stub, $class);
        $stub = $this->replaceNamespace($stub, $namespace);
        $stub = $this->replaceName($stub, $name);
        $stub = $this->replaceLowerName($stub, $lowername);
        $stub = $this->replacePlural($stub, $plural);

        $stub = $this->replaceLowerPlural($stub, $lowerplural);

        $this->file->put("{$service_path}/{$class}.php", $stub);
    }

    public function makeInterface($module, $module_path, $namespace)
    {
        $plural = Str::plural($module);
        $class = $module.'Interface';
        $interface_path = "{$module_path}/Repositories";
        if (!$this->file->exists($interface_path)) {
            mkdir($interface_path, 0777, true);
        }
        // The stub file
        $stub_path = $this->moduleStub('repo-interface');

        $stub = $this->file->get($stub_path);
        $stub = $this->replaceClass($stub, $class);
        $stub = $this->replaceNamespace($stub, $namespace);

        $this->file->put("{$interface_path}/{$class}.php", $stub);
    }

    public function makeEloquentRepo($module, $module_path, $namespace)
    {
        $plural = Str::plural($module);
        $class = $module;
        $repo_path = "{$module_path}/Repositories/Eloquent";
        if (!$this->file->exists($repo_path)) {
            mkdir($repo_path, 0777, true);
        }
        // The stub file
        $stub_path = $this->moduleStub('repository');
        $stub = $this->file->get($stub_path);
        $stub = $this->replaceName($stub, $class);
        $stub = $this->replacePlural($stub, $plural);
        $stub = $this->replaceNamespace($stub, $namespace);

        $this->file->put("{$repo_path}/{$class}Repository.php", $stub);
    }

    protected function makeProvider($module, $module_path, $namespace)
    {
        $plural = Str::plural($module);

        $name = ucfirst($module);

        $stub_path = $this->moduleStub('provider');


        $stub = $this->file->get($stub_path);

        $stub = $this->replaceNamespace($stub, $namespace);
        $stub = $this->replaceName($stub, $name);
        $stub = $this->replacePlural($stub, $plural);

        $provider_path = "{$module_path}/Providers";
        mkdir($provider_path, 0777, true);
        file_put_contents("{$provider_path}/{$plural}ServiceProvider.php", $stub);
    }

    protected function makeEventProvider($module, $module_path, $namespace)
    {
        $plural = Str::plural($module);
        $namespace = $namespace;
        $name = ucfirst($module);

        $stub_path = $this->moduleStub('event-provider');

        $stub = $this->file->get($stub_path);

        $stub = $this->replaceNamespace($stub, $namespace);
        $stub = $this->replaceName($stub, $name);
        $stub = $this->replacePlural($stub, $plural);

        $provider_path = "{$module_path}/Providers";
        //mkdir($provider_path, 0777, true);
        file_put_contents("{$provider_path}/{$plural}EventServiceProvider.php", $stub);
    }

    public function emptyFolder($module_path)
    {
        $folders = array(
            'Listeners', 'Traits', 'Events', 'Mail'
        );

        foreach ($folders as $folder) {
            $model_path = "{$module_path}/$folder";
            mkdir($model_path, 0777, true);
        }
    }

    protected function makeResources($module_path, $module)
    {
        $module_path = "{$module_path}/Resources";
        $lower_plural = strtolower(Str::plural($module));

        $view_path = $module_path.'/Views';
        $lang_path = $module_path.'/Lang/en';
        mkdir($view_path, 0777, true);
        mkdir($lang_path, 0777, true);

        $stub_path = $this->moduleStub('view');
        $form_stub_path = $this->moduleStub('form');
        // Index page stub
        $stub = $this->file->get($stub_path);
        $stub = $this->replaceLowerPlural($stub, $lower_plural);
        $stub = $this->replaceName($stub, $module);
        // Create and edit stub
        $form_stub = $this->file->get($form_stub_path);
        $form_stub = $this->replaceName($form_stub, $module);
        $form_stub = $this->replaceLowerPlural($form_stub, $lower_plural);

        file_put_contents("{$view_path}/index.blade.php", $stub);
        file_put_contents("{$view_path}/form.blade.php", $form_stub);
    }

    protected function makeRequests($module, $module_path, $namespace)
    {
        foreach (range(1, 2) as $number) {
            $class = '';
            switch ($number) {
                case 1:
                    $class = "Store{$module}Request";
                    break;
                case 2:
                    $class = "Update{$module}Request";
                    break;
            }
            $this->call('make:request', ['name' => $namespace."\\Http\\Requests\\${class}"]);
        }
    }

    private function resourceRoute($name)
    {
        $module = Str::plural(strtolower($name));
        $stub_path = $this->moduleStub('resource-route');

        $stub = $this->file->get($stub_path);

        $stub = $this->replaceLowerName($stub, $module);
        $stub = $this->replacePlural($stub, Str::plural($name));
        $stub = $this->replaceName($stub, $name);

        return $stub;
    }

    private function route($plural)
    {
        $namespace = 'App\Modules\\'.ucfirst($plural).'\Http\Controllers';
        $stub_path = $this->moduleStub('route');

        $stub = $this->file->get($stub_path);

        $stub = $this->replaceNamespace($stub, $namespace);

        return $stub;
    }

    private function makeCoreModule()
    {

        $core_path = 'App/Modules/Core';
        $this->file->makeDirectory($core_path);

        // Create abstract repository
        $repo_path = $core_path.'/Repositories';
        $this->file->makeDirectory($repo_path);
        $stub_path = $this->moduleStub('abstract-repo');
        $stub = $this->file->get($stub_path);
        $this->file->put($repo_path.'/AbstractRepo.php', $stub);

        // Create helper file
//        $helper_path = $core_path.'/Helpers';
//        $this->file->makeDirectory($helper_path);
//        $stub_path = $this->moduleStub('helper');
//        $stub = $this->file->get($stub_path);
//        $this->file->put($helper_path.'/helpers.php', $stub);
    }
}
