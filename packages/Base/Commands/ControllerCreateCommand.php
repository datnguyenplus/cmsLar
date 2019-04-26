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

class ControllerCreateCommand extends Command
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
    protected $module;

    /**
     * @var
     */
    protected $location;

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'module:make-controller {name : The module that you want to create} {--force : Overwrite any existing files.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a module in the /packages directory.';

    /**
     * @var Composer
     */
    protected $composer;

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
        if (!preg_match('/^[a-z\-]+$/i', $this->argument('name'))) {
            $this->error('Only alphabetic characters are allowed.');
            return false;
        }

        $this->module = $this->argument('name');
        $this->location = config('base.module_path') . '/' . ucfirst($this->module);

        if ($this->files->isDirectory($this->location)) {
            $this->error('A module named [' . $this->module . '] already exists.');
            return false;
        }

        $this->publishStubs();
        $this->renameModelsAndRepositories($this->location);
        $this->searchAndReplaceInFiles();
        $this->line('------------------');
        $this->line('The module' . studly_case($this->module) . 'was created in [packages/' . $this->module . ']!');
        $this->line('------------------');
        $this->call('optimize');
        return true;
    }

    /**
     * Generate the module in Modules directory.
     * @author Dat Nguyen
     */
    private function publishStubs()
    {
        $from = base_path('packages/Base/Stubs');

        if ($this->files->isDirectory($from)) {
            $this->publishDirectory($from, $this->location);
        } else {
            $this->error('Can’t locate path: <' . $from . '>');
        }
    }

    /**
     * Search and replace all occurrences of ‘Module’
     * in all files with the name of the new module.
     * @author Dat Nguyen
     */
    public function searchAndReplaceInFiles()
    {

        $manager = new MountManager([
            'directory' => new Flysystem(new LocalAdapter($this->location)),
        ]);

        foreach ($manager->listContents('directory://', true) as $file) {
            if ($file['type'] === 'file') {
                $content = str_replace(['{Module}', '{module}', '{MODULE}', '{migrate_date}'], 
                    [ucfirst($this->module), strtolower($this->module), strtolower($this->module), Carbon::now()->format('Y_m_d_His')], $manager->read('directory://' . $file['path']));
                $manager->put('directory://' . $file['path'], $content);
            }
        }
    }

    /**
     * Rename models and repositories.
     * @param $location
     * @return boolean
     * @author Dat Nguyen
     */
    public function renameModelsAndRepositories($location)
    {
        $paths = scan_folder($location);
        if (empty($paths)) {
            return false;
        }
        foreach ($paths as $path) {
            $path = $location . DIRECTORY_SEPARATOR . $path;

            $newPath = $this->transformFilename($path);
            rename($path, $newPath);

            $this->renameModelsAndRepositories($newPath);
        }
        return true;
    }

    /**
     * Rename file in path.
     *
     * @param string $path
     * @return string
     * @author Dat Nguyen
     */
    public function transformFilename($path)
    {
        return str_replace(
            ['{Module}', '{module}', '.stub', '{migrate_date}'],
            [ucfirst($this->module), strtolower($this->module), '.php', Carbon::now()->format('Y_m_d_His')],
            $path
        );
    }

    /**
     * Publish the directory to the given directory.
     *
     * @param string $from
     * @param string $to
     * @return void
     * @author Dat Nguyen
     */
    protected function publishDirectory($from, $to)
    {
        $manager = new MountManager([
            'from' => new Flysystem(new LocalAdapter($from)),
            'to' => new Flysystem(new LocalAdapter($to)),
        ]);

        foreach ($manager->listContents('from://', true) as $file) {
            if ($file['type'] === 'file' && (!$manager->has('to://' . $file['path']) || $this->option('force'))) {
                $manager->put('to://' . $file['path'], $manager->read('from://' . $file['path']));
            }
        }
    }

    /**
     * Create the directory to house the published files if needed.
     *
     * @param string $directory
     * @return void
     * @author Dat Nguyen
     */
    protected function createParentDirectory($directory)
    {
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }
}
