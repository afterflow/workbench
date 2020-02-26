<?php


namespace Afterflow\Workbench;


class PackagistApi {

    public function getRepository( $vendor, $package ) {

        $data = json_decode( file_get_contents( 'https://packagist.org/search.json?q=' . $vendor . '/' . $package ), true );
        if ( count( $data[ 'results' ] ) ) {
            return $data[ 'results' ][ 0 ][ 'repository' ];
        }
    }

}
