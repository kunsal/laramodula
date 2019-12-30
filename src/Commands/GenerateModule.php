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
    protected $signature = 'make:module {name} {--schema=} {--empty} {--type=} {--form=}';

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
    public function handle() {
        // Create Modules folder in laravel app directory
        if (!$this->file->exists('app/Modules')) {
            $this->file->makeDirectory('app/Modules');
        }
        $module = $this->argument('name');
        $schema = $this->option('schema');
        $empty = $this->option('empty');
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
        $this->makeModel($module, $module_path,$namespace, $schema);
        $this->makeInterface($module, $module_path,$namespace);
        $this->makeProvider($module, $module_path,$namespace);
        $this->makeController($module, $module_path, $namespace, $empty, $type);
        $this->makeService($module, $module_path, $namespace);
        $this->makeEventProvider($module, $module_path, $namespace);
        $this->makeResources($module_path, $module);
        $this->empty_folders($module_path);
    }

    // Get stub file
    protected function moduleStub($filename){
        return __DIR__ . "/stubs/{$filename}.stub";
    }

    protected function makeController($module, $module_path, $namespace, $empty=true, $type='')
    {
        // The stub file
        $stub_path = $this->moduleStub('controllers');

        // Data to replace in stub
        $plural = Str::plural($module);
        $lowerplural = strtolower($plural);
        $class =  $plural."Controller";
        $name = ucfirst($module);
        $namespace = "namespace {$namespace}\\Http\\Controllers";
        $lowername = strtolower($module);
        // Path to directory to create file in
        $controller_path = "{$module_path}/Http/Controllers";
        // Create directory with read, write, execute
        mkdir($controller_path, 0777, true);
        $stub = $this->file->get($stub_path);
        $stub = $this->replaceClass($stub, $class);
        $stub = $this->replaceNamespace($stub, $namespace);
        $stub = $this->replaceName($stub, $name);
        $stub = $this->replaceLowerName($stub, $lowername);
        $stub = $this->replacePlural($stub, $plural);
        $stub = $this->replaceType($stub, $type);
        $stub = $this->replaceLowerPlural($stub, $lowerplural);

        $this->file->put("{$controller_path}/{$class}.php", $stub);

        file_put_contents("{$module_path}/Http/routes.php", ($empty == false) ? $this->resource_route($plural) : $this->route($plural));
        $this->makeRequests($module, $module_path, $namespace);
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

    protected function makeModel($module, $module_path,$namespace, $schema=null)
    {
        $plural = Str::plural($module);
        $class = ucfirst($module);
        $model_path = "{$module_path}/Models/Repositories";
        mkdir($model_path, 0777, true);
        // The stub file
        $stub_path = $this->moduleStub('model');

        $stub = $this->file->get($stub_path);
        $stub = $this->replaceClass($stub, $class);
        $stub = $this->replaceNamespace($stub, $namespace);
        $stub = $this->replacePlural($stub, $plural);
        // If user is being generated
        if(strtolower($module) == 'user'){
            $stub = str_replace('extends Model', 'extends Authenticable', $stub);
            $stub = str_replace('Illuminate\Database\Eloquent\Model;', 'Illuminate\Foundation\Auth\User as Authenticable;', $stub);
        }

        $this->file->put("{$model_path}/{$class}.php", $stub);

        if(!is_null($schema)){
            $this->call('make:migration:schema', ['name' => 'create_'.strtolower($plural).'_table', '--schema' => $schema, '--model'=>0]);
        }

//        if(!is_null($form)){
//            $this->call('make:form', ['name' => $name_space.'\\Forms\\'.$class.'Form', '--fields' => $form]);
//        }else{
//            $this->call('make:form', ['name' => $name_space.'\\Forms\\'.$class.'Form']);
//        }

    }

    public function makeInterface($module, $module_path,$namespace) {
        $plural = Str::plural($module);
        $class = ucfirst($module).'Interface';
        $interface_path = "{$module_path}/Models";
        if (!$this->file->exists($interface_path)) {
            mkdir($interface_path, 0777, true);
        }
        // The stub file
        $stub_path = $this->moduleStub('interface');

        $stub = $this->file->get($stub_path);
        $stub = $this->replaceClass($stub, $class);
        $stub = $this->replaceNamespace($stub, $namespace);

        $this->file->put("{$interface_path}/{$class}.php", $stub);
    }

    protected function makePresenter($module, $module_path,$namespace)
    {
        $class = ucfirst($module)."Presenter";
        $namespace = $namespace.'\\Presenters';
        $stub_path = $this->moduleStub('presenter');

        $stub = $this->file->get($stub_path);
        $stub = $this->replaceClass($stub, $class);
        $stub = $this->replaceNamespace($stub, $namespace);

        $presenter_path = "{$module_path}/Presenters";
        mkdir($presenter_path, 0777, true);
        file_put_contents("{$presenter_path}/{$module}Presenter.php", $stub);
    }

    protected function makeProvider($module, $module_path,$namespace)
    {
        $plural = Str::plural($module);
        $namespace = $namespace.'\\Providers';
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

    protected function makeEventProvider($module, $module_path,$namespace)
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

    public function empty_folders($module_path)
    {
        $folders = array(
            'Listeners', 'Traits', 'Events', 'Mail'
        );

        foreach($folders as $folder){
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

    protected function makeRequests($module, $module_path,$namespace)
    {
        $namespace = 'App\Modules\\'.Str::plural(ucfirst($module)).'\Http\Requests';
        $request_path = "{$module_path}/Http/Requests";
        mkdir($request_path, 0777, true);

        foreach(range(1,2) as $number){
            $class = '';
            switch($number){
                case 1:
                    $class = "Store{$module}Request";
                    break;
                case 2:
                    $class = "Update{$module}Request";
                    break;
            }
            $stub_path = $this->moduleStub('requests');

            $stub = $this->file->get($stub_path);

            $stub = $this->replaceClass($stub, $class);
            $stub = $this->replaceNamespace($stub, $namespace);

            file_put_contents("{$request_path}/{$class}.php", $stub);
        }
    }

    private function resource_route($plural)
    {
        $module = strtolower($plural);
        $stub_path = $this->moduleStub('resource-route');

        $stub = $this->file->get($stub_path);

        $stub = $this->replaceName($stub, $module);
        $stub = $this->replacePlural($stub, $plural);

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
}
