<?php

namespace Afterflow\Workbench;

use Afterflow\Workbench\Console\WorkbenchPull;
use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider {
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        $this->commands( [
            WorkbenchPull::class,
        ] );

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {
        //
    }
}
