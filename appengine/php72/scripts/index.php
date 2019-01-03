<?php

echo "Scripts Test!<br />";

echo "is /usr/lib/php/extensions/no-debug-non-zts-20170718 a dir?";
var_dump($exists = file_exists('/usr/lib/php/extensions/no-debug-non-zts-20170718'));
echo "<br />";

if ($exists) {
    exec('ln -s /usr/lib/php/extensions/no-debug-non-zts-20170718', $output);
    var_dump($output);
    echo "<br />";
}

echo "decrypted file exists?";
var_dump(file_exists('secrets.txt'));
var_dump(file_get_contents('secrets.txt'));
echo "<br />";

echo "extension loaded?";
var_dump(extension_loaded('swoole'));
echo "<br />";

echo "extension exists?";
var_dump(file_exists('swoole.so'));
echo "<br />";
echo "<br />";
// var_dump(file_exists(__DIR__ . '/test.txt'));

// var_dump(file_get_contents(__DIR__ . '/test.txt'));

// var_dump(getenv('BRENT_ENV'));

phpinfo();
