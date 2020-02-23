<?php

namespace Afterflow\Workbench\Console;

use Afterflow\Framework\ComposerJson;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class WorkbenchPull extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workbench:pull {vendorName} {--ssh}';

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
        $vendorName = $this->argument( 'vendorName' );
        $vendor     = explode( '/', $vendorName )[ 0 ];
        $package    = explode( '/', $vendorName )[ 1 ];

        $this->line( 'Pulling ' . $vendorName );
        $data = json_decode( file_get_contents( 'https://packagist.org/search.json?q=' . $vendorName ), true );
        if ( count( $data[ 'results' ] ) ) {
            $repo = $data[ 'results' ][ 0 ][ 'repository' ];
        } else {
            $repo = 'https://github.com/' . $vendorName;
        }
        $this->line( 'Found repository: ' . $repo );

        $parts              = collect( explode( '/', $repo ) )->reverse()->take( 2 )->reverse();
        $githubVendor       = $parts->first();
        $githubName         = $parts->last();
        $vendorNameOnGithub = $githubVendor . '/' . $githubName;

        $origin = 'https://github.com/' . $vendorNameOnGithub . '.git';
        if ( $this->option( 'ssh' ) ) {
            $origin = 'git@github.com:' . $vendorNameOnGithub . '.git';
        }

        $dir = base_path();

        if ( ! file_exists( base_path( 'workbench/' . $vendor . '/' . $package ) ) ) {
            $this->line( 'Pulling ' . $origin . ' into ' . $dir );
            @\File::makeDirectory( $dir . '/workbench/' . $vendor, 0777, true );
            $p = ( new Process( [ 'git', 'clone', $origin ], $dir . '/workbench/' . $vendor ) );
            $p->run();
        } else {

            $this->line( 'Folder exists' );
        }

        $cj = new ComposerJson();
        $cj->addPathRepository( 'workbench/' . $vendorName );
        //        $cj->addRequire( $vendorName );
        //        @\File::deleteDirectory( base_path( 'vendor/' . $vendorName ) );
        //        $p = ( new Process( [ 'composer', 'remove', $vendorName ] ) );
        //        $p->run();
        $p = ( new Process( [ 'composer', 'require', $vendorName, '@dev' ] ) );
        $p->run();
    }

}
