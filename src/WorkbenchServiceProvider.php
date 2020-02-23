<?php

namespace Afterflow\Workbench;

use Afterflow\Workbench\Console\WorkbenchPull;
use Afterflow\Workbench\Console\WorkbenchUnlink;
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
            WorkbenchUnlink::class,
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
