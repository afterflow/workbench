<?php

namespace Afterflow\Workbench\Files;

use Illuminate\Support\Arr;

class ComposerJson extends JsonFile {

    public $path = 'composer.json';

    protected $repositories;
    protected $requirements;
    protected $devRequirements;

    public function __construct( $path ) {
        parent::__construct( $path );
        // Hydrate collections
        $this->repositories    = collect( Arr::get( $this->content, 'repositories', [] ) );
        $this->requirements    = collect( Arr::get( $this->content, 'require', [] ) );
        $this->devRequirements = collect( Arr::get( $this->content, 'require-dev', [] ) );
    }

    public function repositories() {
        return $this->repositories;
    }

    public function devRequirements() {
        return $this->devRequirements;
    }

    public function allRequirements() {
        return $this->requirements()->merge( $this->devRequirements() );
    }

    public function requirements() {
        return $this->requirements;
    }

    public function write() {
        $this->content[ 'require' ]      = $this->requirements()->toArray();
        $this->content[ 'require-dev' ]  = $this->devRequirements()->toArray();
        $this->content[ 'repositories' ] = $this->repositories()->toArray();

        return parent::write();
    }

    public function removeRepository( $path ) {
        $this->repositories = $this->repositories->keyBy( 'path' )->forget( $path )->values();

        return $this;
    }

    public function addPathRepository( $path ) {

        $this->repositories = $this->repositories()->push( [
            'type' => 'path',
            'url'  => $path,
        ] )->keyBy( 'url' )->values();

        return $this;
    }

}
