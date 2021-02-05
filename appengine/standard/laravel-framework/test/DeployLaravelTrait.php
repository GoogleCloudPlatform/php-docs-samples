<?php
/**
 * Copyright 2018 Google LLC
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

namespace Google\Cloud\Samples\AppEngine\Laravel;

use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\FileUtil;

trait DeployLaravelTrait
{
    use ExecuteCommandTrait;

    private static function createLaravelProject()
    {
        // Create the laravel project in a temporary directory
        $tmpDir = sys_get_temp_dir() . '/test-' . FileUtil::randomName(8);

        // install
        $laravelPackage = 'laravel/laravel';
        $cmd = sprintf('composer create-project --no-scripts %s %s', $laravelPackage, $tmpDir);
        $process = self::createProcess($cmd);
        $process->setTimeout(300); // 5 minutes
        self::executeProcess($process);

        // move the code for the sample to the new laravel installation
        self::copyFiles([
            'bootstrap/app.php',
        ], $tmpDir);

        // set the directory in gcloud and move there
        self::setWorkingDirectory($tmpDir);
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        // fix "beyondcode/laravel-dump-server" issue
        file_put_contents(
            'composer.json',
            json_encode(
                array_merge_recursive(
                    json_decode(file_get_contents('composer.json'), true),
                    ['extra' => ['laravel' => ['dont-discover' => 'beyondcode/laravel-dump-server']]]
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        return $tmpDir;
    }

    private static function addAppKeyToAppYaml($targetDir)
    {
        // copy in the app.yaml and add the app key.
        $appYaml = str_replace([
            'YOUR_APP_KEY',
        ], [
            trim(self::execute('php artisan key:generate --show --no-ansi')),
        ], file_get_contents($targetDir . '/app.yaml'));
        file_put_contents($targetDir . '/app.yaml', $appYaml);
    }

    private static function copyFiles(array $files, $dir)
    {
        foreach ($files as $file) {
            $source = sprintf('%s/../%s', __DIR__, $file);
            $target = sprintf('%s/%s', $dir, $file);
            copy($source, $target);
        }
    }
}
