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

namespace Google\Cloud\Samples\AppEngine\Flexible\WordPress;

use Google\Cloud\TestUtils\ExecuteCommandTrait;

trait RunSetupCommandTrait
{
    use ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../wordpress.php';

    private static function runSetupCommand(array $args = [])
    {
        // determine the deployment dir
        $dir = isset($args['--dir'])
            ? $args['--dir']
            : sprintf('%s/wp-gae-flex-%s', sys_get_temp_dir(), time());

        // run the setup command
        self::runCommand('setup', $args + array_filter([
            '--dir' => $dir,
        ]));

        return $dir;
    }
}
