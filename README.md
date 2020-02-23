# Afterflow Workbench

A simple tool to pull packages from composer and work on them locally.

## Installation

```bash
composer require afterflow/workbench
```

## Usage

Find a composer package, fetch it's source from GitHub,
put it under `workbench/vendor/package`, register a path type repository in composer.json and 
tell Composer to symlink the local version.

```bash
php artisan workbench:pull vendor/package --ssh
```

Remove `workbench/vendor/package`, remove the repository from composer.json and switch it to packagist.

When called with `--remove` flag, it will also remove it from "require".

```bash
php artisan workbench:inlink --remove vendor/package
```

