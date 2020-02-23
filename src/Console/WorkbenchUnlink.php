<?php

namespace Afterflow\Workbench\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class WorkbenchUnlink extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workbench:unlink {vendorName}';

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
        $vendorName = $this->argument( 'vendorName' );

        $composerJson = json_decode( file_get_contents( base_path( 'composer.json' ) ), true );
        $composerJson = $this->removeComposerJsonRepo( $composerJson, $vendorName );
        file_put_contents( 'composer.json', json_encode( $composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
        @\File::deleteDirectory( base_path( 'workbench/' . $vendorName ) );

        $p = ( new Process( [ 'composer', 'remove', $vendorName ] ) );
        $p->run();
        $p = ( new Process( [ 'composer', 'require', $vendorName, '@dev' ] ) );
        $p->run();

    }

    protected function removeComposerJsonRepo( $composerJson, $vendorName ) {

        $repos = $composerJson[ 'repositories' ] ?? [];
        foreach ( $repos as &$r ) {
            if ( $r[ 'url' ] == $vendorName ) {
                unset( $r );
            }
        }

        $composerJson[ 'repositories' ] = $repos;

        return $composerJson;
    }
}
