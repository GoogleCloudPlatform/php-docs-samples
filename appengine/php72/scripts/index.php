<?php

echo "Scripts Test!<br />";

echo "is /usr/lib/php/extensions/no-debug-non-zts-20170718 a dir?<br />";
var_dump($exists = file_exists('/usr/lib/php/extensions/no-debug-non-zts-20170718'));

if ($exists) {
    exec('ln -s /usr/lib/php/extensions/no-debug-non-zts-20170718', $output);
    var_dump($output);
}

echo "decrypted file exists? <br />";
var_dump(file_exists('secrets.txt'));
var_dump(file_get_contents('secrets.txt'));

echo "extension loaded? <br />";
var_dump(extension_loaded('swoole'));

echo "extension exists? <br />";
var_dump(file_exists('swoole.so'));

echo "<br />";
// var_dump(file_exists(__DIR__ . '/test.txt'));

// var_dump(file_get_contents(__DIR__ . '/test.txt'));

// var_dump(getenv('BRENT_ENV'));

phpinfo();
