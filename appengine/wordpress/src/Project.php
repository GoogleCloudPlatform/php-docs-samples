<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Helper;

class Project implements ReportInterface
{
    use ReportTrait;
    private $dir;
    public function __construct($dir)
    {
        if (Utils::isRelativePath($dir)) {
            $dir = getcwd() . DIRECTORY_SEPARATOR . $dir;
        }
        if (is_file($dir)) {
            $this->errors[] = 'File exists: ' . $dir;
            return;
        }
        if (is_dir($dir)) {
            $this->info[] = 'Re-using a directory ' . $dir . '.';
        } elseif (!@mkdir($dir, 0750, true)) {
            $this->errors[] = 'Can not create a directory: ' . $dir;
        } else {
            $this->info[] = 'A directory ' . $dir . ' was created.';
        }
        $this->dir = realpath($dir);
    }

    public function downloadArchive($name, $url, $dir='')
    {
        $tmpdir = sys_get_temp_dir();
        $file = $tmpdir . DIRECTORY_SEPARATOR . basename($url);
        file_put_contents($file, file_get_contents($url));
        
        if (substr($url, -3, 3) === 'zip') {
            $zip = new \ZipArchive;
            if ($zip->open($file) === false) {
                $this->errors[] = 'Failed to open a zip file: ' . $file;
                return;
            }
            if ($zip->extractTo($this->dir . $dir) === false) {
                $this->errors[] = 'Failed to extract a zip file: ' . $file;
                $zip->close();
                return;
            }
            $zip->close();
        } else {
            $phar = new \PharData($file, 0, null);
            $phar->extractTo($this->dir . $dir, null, true);
        }
        unlink($file);
        $this->info[] = 'Downloaded ' . $name . '.';
        // TODO error check
    }

    public function copyFiles($path, $files, $params)
    {
        $loader = new \Twig_Loader_Filesystem($path);
        $twig = new \Twig_Environment($loader);
        foreach ($files as $file => $target) {
            $dest = $this->dir . $target . $file;
            touch($dest);
            chmod($dest, 0640);
            $content = $twig->render($file, $params);
            file_put_contents($dest, $content, LOCK_EX);
        }
        $this->info[] = 'Copied necessary files with parameters.';
    }

    public function runComposer()
    {
        chdir($this->dir);
        exec(
            'composer update --no-interaction --no-progress --no-ansi',
            $output, $ret);
        $this->info = array_merge($this->info, $output);
        if ($ret !== 0) {
            $this->info[] = 'Failed to run composer update in ' . $dir
                . '. Please run it by yourself before running WordPress.';
        }
    }

    public function getDir()
    {
        return $this->dir;
    }
}
