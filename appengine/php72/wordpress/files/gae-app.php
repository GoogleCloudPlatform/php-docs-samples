<?php

function get_real_file_to_load($full_request_uri)
{
    $request_uri = @parse_url($full_request_uri)['path'];

    if ($request_uri === '/wp-admin') {
        header('Location: /wp-admin/');
        exit;
    } elseif ($request_uri === '/wp-admin/') {
        return '/wp-admin/index.php';
    } elseif (is_file(__DIR__ . $request_uri)) {
        return $request_uri;
    }
    return '/index.php';
}

// fixes b/111391534
$_SERVER['HTTPS'] = $_SERVER['HTTP_X_APPENGINE_HTTPS'];

// Loads the expected WordPress framework file
// (e.g index.php, wp-admin/* or wp-login.php)
$file = get_real_file_to_load($_SERVER['REQUEST_URI']);

// Set the environment variables to reflect the script we're loading
// (in order to trick WordPress)
$_SERVER['DOCUMENT_URI']    = $_ENV['DOCUMENT_URI']    = $file;
$_SERVER['PHP_SELF']        = $_ENV['PHP_SELF']        = $file;
$_SERVER['SCRIPT_NAME']     = $_ENV['SCRIPT_NAME']     = $file;
$_SERVER['SCRIPT_FILENAME'] = $_ENV['SCRIPT_FILENAME'] = __DIR__ . $file;

require __DIR__ . $file;
