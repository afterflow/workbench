<?php

namespace Afterflow\Workbench\Console;

use Afterflow\Framework\ComposerJson;
use Afterflow\Recipe\Recipe;
use Afterflow\Workbench\Composer;
use Afterflow\Workbench\Folders\WorkbenchFolder;
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
        [ $vendor, $package ] = explode( '/', $this->argument( 'vendorName' ) );

        $this->line( 'Crafting ' . $this->argument( 'vendorName' ) );

        $input = [
            'vendor'       => $vendor,
            'package'      => $package,
            'vendorTitle'  => $this->ask( 'Vendor title?', ucfirst( $vendor ) ),
            'packageTitle' => $this->ask( 'Package title?', ucfirst( $package ) ),
            'description'  => $this->ask( 'Description?', 'Work in progress' ),
            'authorName'   => $this->ask( 'Author name?', 'Vladislav' ),
            'authorEmail'  => $this->ask( 'Author email?', 'vlad@serpentine.io' ),
            'addLaravel'   => $this->confirm( 'Add Laravel integration?' ),
        ];

        $this->alert( 'Crafting package' );

        $workbench = new WorkbenchFolder();

        $base_path = $workbench->packagePath( $vendor, $package );

        $this->copyStub( __DIR__ . '/../../stubs/package', $base_path );

        $data = Recipe::make( $input )->template( $base_path . '/composer.json.blade.php' )->render();
        unlink( $base_path . '/composer.json.blade.php' );
        file_put_contents( $base_path . '/composer.json', $data );

        $data = Recipe::make( $input )->template( $base_path . '/README.md.blade.php' )->render();
        unlink( $base_path . '/README.md.blade.php' );
        file_put_contents( $base_path . '/README.md', $data );

        $data = Recipe::make( $input )->template( $base_path . '/phpunit.xml.dist.blade.php' )->render();
        unlink( $base_path . '/phpunit.xml.dist.blade.php' );
        file_put_contents( $base_path . '/phpunit.xml.dist', $data );

        $data = Recipe::make( $input )->template( $base_path . '/tests/BasicTest.php.blade.php' )->render();
        unlink( $base_path . '/tests/BasicTest.php.blade.php' );
        file_put_contents( $base_path . '/tests/BasicTest.php', $data );

        if ( $input[ 'addLaravel' ] ) {
            $data = Recipe::make( $input )->template( $base_path . '/src/ServiceProvider.php.blade.php' )->render();
            file_put_contents( $base_path . '/src/' . $input[ 'packageTitle' ] . 'ServiceProvider.php', $data );
        }

        unlink( $base_path . '/src/ServiceProvider.php.blade.php' );

        $this->info( 'Package generated, enabling it...' );

        $composer = new Composer();
        $composer->json()->addPathRepository( $base_path )->write();
        $composer->require( $vendor, $package );

        $this->info( 'Done!' );

    }

    public function copyStub( $from, $to = null ) {

        if ( is_dir( $from ) ) {
            return \File::copyDirectory( $from, $to );
        }

        return \File::copy( $from, $to );
    }

}
