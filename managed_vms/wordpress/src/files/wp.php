<?php
/**
 * Copyright (c) <2012> <Ripeworks>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

/**
 * This file is originally hosted at:
 * http://ripeworks.com/run-wordpress-locally-using-phps-buily-in-web-server/
 */

// This file is a front controller for running your WordPress locally with
// PHP's built-in web server. This allows you to maintain the plugins/themes
// in the UI when running locally.

$root = $_SERVER['DOCUMENT_ROOT'];
chdir($root);
$path = '/' . ltrim(parse_url($_SERVER['REQUEST_URI'])['path'], '/');
set_include_path(get_include_path() . ':' . __DIR__);
if (file_exists($root . $path)) {
    if (is_dir($root . $path) && substr($path, strlen($path) - 1, 1) !== '/') {
        $path = rtrim($path, '/') . '/index.php';
    }
    if (strpos($path, '.php') === false) {
        return false;
    } else {
        chdir(dirname($root . $path));
        require_once $root . $path;
    }
} else {
    include_once 'index.php';
}
