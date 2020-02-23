<?php

namespace Afterflow\Workbench\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class WorkbenchNew extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workbench:new {vendorName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init a new package';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $vendorName = $this->argument( 'vendorName' );
        $vendor     = explode( '/', $vendorName )[ 0 ];
        $package    = explode( '/', $vendorName )[ 1 ];

        $this->line( 'Crafting ' . $vendorName );
    }

}
