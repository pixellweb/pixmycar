<?php

namespace Citadelle\PixMyCar;


use Citadelle\PixMyCar\app\Console\Commands\Import;
use Illuminate\Support\ServiceProvider;

class PixMyCarServiceProvider extends ServiceProvider
{

    protected $commands = [
        Import::class,
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addCustomConfigurationValues();
    }

    public function addCustomConfigurationValues()
    {
        // add filesystems.disks for the log viewer
        config([
            'logging.channels.pixmycar' => [
                'driver' => 'single',
                'path' => storage_path('logs/pixmycar.log'),
                'level' => 'debug',
            ]
        ]);

    }


    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/pixmycar.php', 'pixmycar'
        );

        // register the artisan commands
        $this->commands($this->commands);
    }
}
