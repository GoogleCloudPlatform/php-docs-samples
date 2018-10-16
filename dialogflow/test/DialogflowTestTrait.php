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

namespace Google\Cloud\Samples\Dialogflow;

use Symfony\Component\Console\Tester\CommandTester;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExponentialBackoffTrait;

trait DialogflowTestTrait
{
    use TestTrait;
    use ExponentialBackoffTrait;

    /** @before */
    public function setupDialogFlowTest()
    {
        $this->useResourceExhaustedBackoff(6);
    }

    private function runCommand($commandName, array $args = [])
    {
        $application = require __DIR__ . '/../dialogflow.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        $args['project-id'] = self::$projectId;

        // run in exponential backoff in case of Resource Exhausted errors.
        return self::$backoff->execute(function () use ($commandTester, $args) {
            ob_start();
            $commandTester->execute($args, ['interactive' => false]);
            return ob_get_clean();
        });
    }
}
