<?php 
namespace TTSoft\Base\Commands;
/*https://stackoverflow.com/questions/21641606/laravel-running-migrations-on-app-database-migrations-folder-recursively*/
use Artisan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\MountManager;

class CallMigrationCommand extends Command
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
    protected $signature = 'module:migrate {name : The module that you want to create} {--force : Overwrite any existing files.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call Migrate of module';

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
        $this->location = config('base.module_path') . '/' . ucfirst($this->module).'/Databases/migrations';
        \Artisan::call('migrate', [
           '--path' => $this->location,
        ]);
        $this->line('------------------');
        $this->line('Migrate successful!');
        $this->line('------------------');
        $this->call('optimize');
        return true;
    }
}

