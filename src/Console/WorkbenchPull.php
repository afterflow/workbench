<?php

namespace Afterflow\Workbench\Console;

use Afterflow\Framework\ComposerJson;
use Afterflow\Workbench\Composer;
use Afterflow\Workbench\Folders\VendorFolder;
use Afterflow\Workbench\Folders\WorkbenchFolder;
use Afterflow\Workbench\GitHubRepository;
use Afterflow\Workbench\PackagistApi;
use Afterflow\Workbench\VendorPackageFolder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class WorkbenchPull extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workbench:pull {vendorName} {version=@dev} {--ssh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull a composer package and work on it locally';

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

        $this->line( 'Pulling ' . $vendor . '/' . $package );
        $packagistApi = new PackagistApi();
        if ( $repo = $packagistApi->getRepository( $vendor, $package ) ) {
        } else {
            $repo = $this->askWithCompletion( 'Package ' . $vendor . '/' . $package . ' not found on Packagist. Please provide a GitHub repository URL', [
                GitHubRepository::make( $vendor, $package )->toUrl(),
            ], GitHubRepository::make( $vendor, $package )->toUrl() );
        }

        $gh = GitHubRepository::fromUrl( $repo );

        if ( ! $gh->valid() ) {
            $this->error( 'Not a valid GitHub repository' );

            return;
        }

        $origin = $this->option( 'ssh' ) ? $gh->toSsh() : $gh->toHttps();

        $workbench = new WorkbenchFolder();
        $workbench->createFolder();


        if ( $workbench->packageFolderExists( $vendor, $package ) ) {
            if ( ! $this->confirm( 'Path ' . $workbench->packagePath( $vendor, $package ) . ' exists. Overwrite?' ) ) {
                return $this->line( 'Pulling canceled.' );
            }
            $workbench->deletePackageFolder( $vendor, $package );
        }

        $this->line( 'Cloning ' . $origin . ' into ' . $workbench->packagePath( $vendor, $package ) );

        $workbench->createPackageFolder( $vendor, $package );

        if ( $this->confirm( 'Delete .git folder to prevent adding submodule?' ) ) {
            File::deleteDirectory( $workbench->packagePath( $vendor, $package ) . '/.git' );
        }

        $vendorFolder = new VendorFolder();
        $this->line( 'Deleting ' . $vendorFolder->packagePath( $vendor, $package ) );
        $vendorFolder->deletePackageFolderIfExists( $vendor, $package );

        $p = ( new Process( [ 'git', 'clone', $origin ], $workbench->vendorPath( $vendor ) ) );
        $p->run();

        if ( $p->getExitCode() > 0 ) {
            $this->error( 'Git clone exited with status code ' . $p->getExitCode() );
            $this->error( $p->getErrorOutput() );
        }

        $this->line( 'Updating composer.json' );

        $composer = new Composer();
        $composer->json()->addPathRepository( $workbench->packagePath( $vendor, $package ) )->write();
        $composer->require( $vendor, $package, $this->argument( 'version' ) );
    }

}
