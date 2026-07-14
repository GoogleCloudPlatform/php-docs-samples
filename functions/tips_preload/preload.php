<?php

require_once __DIR__ . '/vendor/autoload.php';

// By running "composer dump-autoload --optimize", the classmap file contains a
// mapping of optimized class files.
$composerClassmap = __DIR__ . '/vendor/composer/autoload_classmap.php';

$classesToPreload = require $composerClassmap;

foreach ($classesToPreload as $class => $file) {
    require_once $file;
}
