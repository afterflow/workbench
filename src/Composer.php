<?php


namespace Afterflow\Workbench;


use Afterflow\Workbench\Files\ComposerJson;
use Symfony\Component\Process\Process;

class Composer {

    protected $path;

    public function __construct( $path = null ) {
        $this->path = $path ? $path : base_path();
    }

    public function json() {
        return new ComposerJson( $this->path . DIRECTORY_SEPARATOR . 'composer.json' );
    }

    public function remove( $vendor, $package ) {
        return $this->command( 'remove ' . $vendor . '/' . $package );
    }

    public function require( $vendor, $package, $version = '@dev' ) {

        return $this->command( 'require ' . $vendor . '/' . $package . ' ' . $version );

    }

    public function command( string $cmd ) {
        $process = Process::fromShellCommandline( 'composer ' . $cmd, base_path() );
        //    $process->setTty( Process::isTtySupported() );
        $process->run( function ( $o, $e ) {
            // only works without TTY
            echo( $e );
        } );

        return $process->getOutput();
    }


}
