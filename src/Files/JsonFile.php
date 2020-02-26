<?php

namespace Afterflow\Workbench\Files;

class JsonFile {

    protected $path;
    protected $content;

    public function __construct( $path ) {
        $this->path    = $path;
        $this->content = $this->read();
    }

    public function content( $value = null ) {

        if ( $value ) {
            $this->content = $value;

            return $this;
        }

        return $this->content;

    }

    public function read() {
        $this->content = json_decode( file_get_contents( $this->path ), true );

        return $this->content;
    }

    public function write() {
        file_put_contents( $this->path, json_encode( $this->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
    }

}
