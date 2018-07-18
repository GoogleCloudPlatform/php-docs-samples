<?php

// Static list provides security against URL injection by default.
$routes = [
    'spanner',
    'monitoring',
];

// Keeping things fast for a small number of routes.
$regex = '/\/(' . join($routes, '|') . ')\.php/';
if (preg_match($regex, $_SERVER['REQUEST_URI'], $matches))
{
    $file_path = __DIR__ . $matches[0];
    if (file_exists($file_path))
    {
        require($file_path);
        return;
    }
}

// The homepage is the default behavior.
// By checking for $_SERVER['REQUEST_URI'] == '/' this could be extended
// to include a file not found page.
require './home.php';
