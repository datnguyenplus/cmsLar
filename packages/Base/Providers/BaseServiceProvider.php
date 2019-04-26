<?php
namespace TTSoft\Base\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;


class BaseServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        \TTSoft\Base\Commands\ModuleCreateCommand::class,
        \TTSoft\Base\Commands\RoleBuildCommand::class,
        \TTSoft\Base\Commands\ModelCreateCommand::class,
        \TTSoft\Base\Commands\MigrationCreateCommand::class,
        \TTSoft\Base\Commands\ControllerCreateCommand::class,
        \TTSoft\Base\Commands\CallMigrationCommand::class,
    ];
    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'admin' => \TTSoft\Base\Http\Middleware\AdminMiddleware::class,
        'lang' => \TTSoft\Base\Http\Middleware\LangMiddleware::class,
    ];
    /**
     * Boot the register provider.
     *
     * @return void
     */
    
    protected $registerProvider = [
        \TTSoft\Base\Providers\BaseRouteServiceProvider::class,
        \TTSoft\Base\Providers\BaseEventServiceProvider::class,
    ];
    /**
     * Boot the service provider.
     *
     * @return void
     */
    

    public function boot()
    {
        $this->registerHelpers();
        
        $this->registerDefaultStringLength();

        $this->registerAppServices();

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'base');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'base');

        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'base');

        $this->loadMigrationsFrom(__DIR__.'/../Databases/migrations');
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouteMiddleware();
        $this->commands($this->commands);
    }

    /**
     * Register registerRouteMiddleware
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        foreach ($this->routeMiddleware as $key => $value) {
            app('router')->aliasMiddleware($key, $value);
        }
    }


    /**
     * set default string length
     * 
     * @return void
     */
    protected function registerDefaultStringLength(){
        Schema::defaultStringLength(191);
    }

    /**
     *
     * Register Service Provider
     *
     */
    protected function registerAppServices(){
        $provider = array_merge($this->registerProvider,folder_views());
        foreach ($provider as $value) {
            $this->app->register($value);
        }
    }

    /**
     *
     * Function Helper Autoload File
     *
     */
    public function registerHelpers(){
        if (file_exists($file = __DIR__.'/../Helpers/Helpers.php')){
            require $file;
        }
    }
    
}