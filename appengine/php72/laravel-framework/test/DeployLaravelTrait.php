<?php
/**
 * Copyright 2018 Google Inc.
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
        $files = [
            // '.gcloudignore',
            'bootstrap/app.php',
            'config/view.php',
        ];
        foreach ($files as $file) {
            $source = sprintf('%s/../%s', __DIR__, $file);
            $target = sprintf('%s/%s', $tmpDir, $file);
            copy($source, $target);
        }

        // set the directory in gcloud and move there
        self::setWorkingDirectory($tmpDir);
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

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
}
