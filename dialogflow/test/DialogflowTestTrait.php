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

use Google\Cloud\Core\ExponentialBackoff;
use Symfony\Component\Console\Tester\CommandTester;

trait DialogflowTestTrait
{
    private static $projectId;
    private static $backoff;

    /** @beforeClass */
    public static function setupDialogFlowTest()
    {
        if (!self::$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Set the GOOGLE_PROJECT_ID environment variable');
        }
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS environment variable');
        }
        self::$backoff = new ExponentialBackoff(5, function ($exception) {
            return $exception instanceof ApiException
                && $exception->getCode() == Code::RESOURCE_EXHAUSTED;
        });
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
