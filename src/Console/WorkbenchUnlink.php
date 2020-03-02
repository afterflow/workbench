<?php

namespace Afterflow\Workbench\Console;

use Afterflow\Workbench\Composer;
use Afterflow\Workbench\Folders\VendorFolder;
use Afterflow\Workbench\Folders\WorkbenchFolder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class WorkbenchUnlink extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workbench:unlink {--remove} {vendorName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the package from workbench and reinstall into vendor';

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
        [ $vendor, $package ] = explode( '/', $this->argument( 'vendorName' ) );
        $composer  = new Composer();
        $workbench = new WorkbenchFolder();
        $v         = new VendorFolder();


        $this->line( 'Removing package from composer' );
        $composer->remove( $vendor, $package );
        $this->line( 'Deleting folder in workbench' );
        $workbench->deletePackageFolder( $vendor, $package );
        $this->line( 'Deleting folder in vendor' );
        $v->deletePackageFolder( $vendor, $package );

        $packagesInVendor = File::directories( $workbench->vendorPath( $vendor ) );
        if ( count( $packagesInVendor ) == 0 ) {
            // remove vendor folder
            File::deleteDirectory( $workbench->vendorPath( $vendor ) );
        }
        $vendorsInWorkbench = File::directories( base_path( 'workbench' ) );
        if ( count( $vendorsInWorkbench ) == 0 ) {
            // remove workbench folder
            File::deleteDirectory( base_path( 'workbench' ) );
        }

        if ( ! $this->option( 'remove' ) ) {
            $this->line( 'Adding package back from packagist' );
            $composer->require( $vendor, $package );
        }

        $this->line( 'Done' );

    }

}
