<?php

namespace TTSoft\Base\Commands;

use Artisan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\MountManager;

class MigrationCreateCommand extends Command
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The name of the module uppercase first character.
     *
     * @var string
     */
    protected $modelName;

    /**
     * @var
     */
    protected $location;

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'module:make-migration {name} {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a model in the packages directory.';

    /**
     * @var Composer
     */
    protected $composer;



    /**
     * @var Module
     */
    protected $module;

    /**
     * Create a new key generator command.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param Composer $composer
     * @author Dat Nguyen
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     * @author Dat Nguyen
     */
    public function handle()
    {

        if (!preg_match('/^[a-z\_]+$/i', $this->argument('name'))) {
            $this->error('Only alphabetic characters are allowed.');
            return false;
        }

        if (!preg_match('/^[a-z\_]+$/i', $this->argument('module'))) {
            $this->error('Only alphabetic characters are allowed.');
            return false;
        }

        $this->modelName = $this->argument('name');
        $this->module = $this->argument('module');

        if (empty($this->modelName)) {
            $this->error('The name model cant empty.');
            return false;
        }
        if (empty($this->module)) {
            $this->error('Module name cant empty.');
            return false;
        }

        $this->location = config('base.module_path') . '/' . ucfirst($this->module).'/Databases/migrations/'.$this->modelName;
        $this->model($this->module,$this->modelName);
        if ($this->files->isDirectory($this->location)) {
            $this->error('A model named [' . $this->modelName . '] already exists.');
            return false;
        }

        $this->line('------------------');
        $this->line('The model' . studly_case($this->modelName) . 'was created in [packages/' .studly_case($this->module).'/Databases/migrations/'. $this->modelName . ']!');
        $this->line('------------------');
        $this->call('optimize');
        return true;
    }

    protected function model($module , $name){
        $className = \Str::studly($name);
        $content = "<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class {$className} extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('{name}s', function (Blueprint {table}) {
            {table}->increments('id');
            {table}->string('name', 120);
            {table}->tinyInteger('status')->unsigned()->default(1);

            {table}->softDeletes();
            {table}->timestamps();
            {table}->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('{name}s');
    }
}
";
        $content = str_replace("{table}", '$table', $content);
        $content = str_replace("{name}", $name, $content);
        $name = \Carbon\Carbon::now()->format('Y_m_d_His').'_'.$name;
        $fp = fopen(base_path("packages/{$module}/Databases/migrations/{$name}.php"),"wb");
        fwrite($fp,$content);
        fclose($fp);
    }
}
