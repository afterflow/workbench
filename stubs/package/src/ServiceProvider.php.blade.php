@startPhp

namespace {{$vendorTitle}}\{{$packageTitle}};

use Illuminate\Support\ServiceProvider;

class {{$packageTitle}}ServiceProvider extends ServiceProvider {
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {

        if ($this->app->runningInConsole()) {
        $this->publishes([
        __DIR__.'/../config/config.php' => config_path('{{$package}}.php'),
        ], 'config');
        }

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', '{{$package}}');
    }
}
