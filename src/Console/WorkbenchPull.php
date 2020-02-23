<?php

namespace Afterflow\Workbench\Console;

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

        $this->line( 'Pulling ' . $origin . ' into ' . $dir );

        @\File::makeDirectory( $dir . '/workbench/' . $vendor, 0777, true );

        $p = ( new Process( [ 'git', 'clone', $origin ], $dir . '/workbench/' . $vendor ) );
        $p->run();

        $composerJson = json_decode( file_get_contents( base_path( 'composer.json') ), true );
        $composerJson = $this->addComposerJsonRepo( $composerJson, $vendorName );
        $composerJson = $this->addComposerJsonRequire( $composerJson, $vendorName );

        file_put_contents( 'composer.json', json_encode( $composerJson, JSON_PRETTY_PRINT ) );

        $p = ( new Process( [ 'composer', 'install', $vendorName ] ) );
        $p->run();
    }

    protected function addComposerJsonRequire( $composerJson, $vendorName ) {

        $repos = $composerJson[ 'require' ] ?? [];
        foreach ( $repos as $name => $r ) {
            if ( $name == $vendorName ) {
                return;
            }
        }
        $repos[ $vendorName ] = '@dev';

        $composerJson[ 'require' ] = $repos;

        return $composerJson;
    }

    protected function addComposerJsonRepo( $composerJson, $vendorName ) {

        $repos  = $composerJson[ 'repositories' ] ?? [];
        $wbPath = 'workbench/' . $vendorName;
        foreach ( $repos as $r ) {
            if ( $r[ 'url' ] == $vendorName ) {
                return;
            }
        }
        $repos[] = [
            'type' => 'path',
            'url'  => $wbPath,
        ];

        $composerJson[ 'repositories' ] = $repos;

        return $composerJson;
    }
}
