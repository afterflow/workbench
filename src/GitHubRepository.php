<?php


namespace Afterflow\Workbench;


class GitHubRepository {

    public $vendor;
    public $package;

    public function __construct( $vendor, $package ) {
        $this->vendor  = $vendor;
        $this->package = $package;
    }

    public static function make( $vendor, $package ) {
        return new static( $vendor, $package );
    }

    public static function fromUrl( $url ) {
        $parts = collect( explode( '/', $url ) )->reverse()->take( 2 )->reverse();

        return static::make( $parts->first(), $parts->last() );
    }

    public function valid() {

        return preg_match( '~https\:\/\/github\.com\/.*?\/.*?~ims', $this->toUrl() );
    }

    public function toUrl() {
        return 'https://github.com/' . $this->vendor . '/' . $this->package;
    }

    public function toHttps() {
        return $this->toUrl() . '.git';
    }

    public function toSsh() {
        return 'git@github.com:' . $this->vendor . '/' . $this->package . '.git';
    }

}
