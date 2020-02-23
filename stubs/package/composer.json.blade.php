{
    "name": "{{$vendor}}/{{$package}}",
    "description": "{{$description}}",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "{{ $authorName }}",
            "email": "{{ $authorEmail }}"
        }
    ],
    "autoload": {
        "psr-4": {
            "{{$vendorTitle}}\\{{$packageTitle}}\\": "src/"
        },
        "files": [
            "helpers.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "{{$vendorTitle}}\\{{$packageTitle}}\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "vendor/bin/phpunit",
        "check-style": "phpcs -p --standard=PSR12 --runtime-set ignore_errors_on_exit 1 --runtime-set ignore_warnings_on_exit 1 src tests",
        "fix-style": "phpcbf -p --standard=PSR12 --runtime-set ignore_errors_on_exit 1 --runtime-set ignore_warnings_on_exit 1 src tests"
    },
    "require": {
    },
    "require-dev": {
        "phpunit/phpunit": "8.*",
        "squizlabs/php_codesniffer": "^3.5"
    }
    @if($addLaravel)
    ,
    "extra": {
        "laravel": {
            "providers": [
                "{{$vendorTitle}}\\{{$packageTitle}}\\{{$packageTitle}}ServiceProvider"
            ]
        }
    }
    @endif
}
