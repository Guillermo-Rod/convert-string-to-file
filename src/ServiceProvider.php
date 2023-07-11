<?php 

namespace GuillermoRod\StringToFile;

use Illuminate\Support\ServiceProvider as LaraverServiceProvider;
use Illuminate\Support\Facades\Blade;
use GuillermoRod\StringToFile\StringToFileObserver;
use GuillermoRod\StringToFile\Services\FileCreatorService;

class ServiceProvider extends LaraverServiceProvider
{    
    public const CONFIG_FILE              = 'string-to-file';
    public const VIEWS_NAMESPACE_ACCESSOR = 'string-to-html-files';
    public const PUBLISH_TAG_NAME         = 'string-to-file';
    

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(StringToFileObserver::class, function ($app) {
            return new StringToFileObserver(new FileCreatorService(), $app['events']);
        });
        
        $this->registerViews();

        $this->registerPublishables();

        $this->registerBladeDirectives();

        $this->registerCommands();
    }

    private function registerViews()
    {
        // Allow views from storage folder
        $diskName = config(self::CONFIG_FILE . '.disk_name');
        $this->loadViewsFrom(config("filesystems.disks.{$diskName}.root"), self::VIEWS_NAMESPACE_ACCESSOR);
    }

    private function registerPublishables()
    {
        // Database table "string_to_html_files"
        $this->publishes([__DIR__ . '/../database/migrations' => database_path('migrations')], [self::PUBLISH_TAG_NAME . ':migrations']);
        
        // Config file
        $this->publishes([__DIR__ . '/../config' => base_path('config')], [self::PUBLISH_TAG_NAME . ':config']);        
    }

    private function registerBladeDirectives()
    {
        Blade::directive('includeHtmlFromString', function ($expression) {
            return "<?php echo \GuillermoRod\StringToFile\BladeDirectives::includeHtmlFromString($expression) ?>";
        });

        Blade::directive('includeFileContentsFromString', function ($expression) {
            return "<?php echo \GuillermoRod\StringToFile\BladeDirectives::includeFileContentsFromString($expression) ?>";
        });

        Blade::directive('includeStyleFromString', function ($expression) {
            return "<?php echo \GuillermoRod\StringToFile\BladeDirectives::includeStyleFromString($expression) ?>";
        });

        Blade::directive('includeScriptFromString', function ($expression) {
            return "<?php echo \GuillermoRod\StringToFile\BladeDirectives::includeScriptFromString($expression) ?>";
        });        
    }

    private function registerCommands()
    {
        $this->commands([
            \GuillermoRod\StringToFile\Commands\RegenerateCommand::class
        ]);
    }
}
