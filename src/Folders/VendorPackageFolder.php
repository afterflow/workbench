<?php


namespace Afterflow\Workbench\Folders;


use Illuminate\Support\Facades\File;

class VendorPackageFolder {

    public $path = '';

    public function __construct( $path = null ) {
        $this->path = $path ?? base_path( $this->path );
    }

    public function createFolder( $path = null ) {
        $path = $path ?? $this->path;
        if ( ! $this->exists( $path ) ) {
            File::makeDirectory( $path ?? $this->path, 0777, true );
        }
    }

    public function exists( $path = null ) {
        $path = $path ?? $this->path;

        return file_exists( $path );
    }

    public function packageFolderExists( $vendor, $package ) {
        return $this->exists( $this->packagePath( $vendor, $package ) );
    }

    public function createPackageFolder( $vendor, $package ) {
        return $this->createFolder( $this->packagePath( $vendor, $package ) );
    }

    public function deletePackageFolderIfExists( string $vendor, string $package ) {

        if ( $this->packageFolderExists( $vendor, $package ) ) {
            $this->deletePackageFolder( $vendor, $package );
        }
    }

    public function deletePackageFolder( $vendor, $package ) {

        File::deleteDirectory( $this->packagePath( $vendor, $package ) );
    }

    public function vendorPath( $vendor ) {
        return $this->path . DIRECTORY_SEPARATOR . $vendor;
    }

    public function packagePath( $vendor, $package ) {
        return $this->vendorPath( $vendor ) . DIRECTORY_SEPARATOR . $package;
    }

    public function hasVendor( $vendor ) {
        return file_exists( $this->vendorPath( $vendor ) );
    }

    public function hasPackage( $vendor, $package ) {
        return file_exists( $this->packagePath( $vendor, $package ) );
    }

}
