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
        $p = ( new Process( [ 'composer', 'remove', $vendor . '/' . $package ] ) );
        $p->run();

        return $p;
    }

    public function require( $vendor, $package, $version = '@dev' ) {
        $p = ( new Process( [ 'composer', 'require', $vendor . '/' . $package, $version ] ) );
        $p->run();

        return $p;
    }

}
