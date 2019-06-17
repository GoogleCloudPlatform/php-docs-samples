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

use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\TestTrait;

trait DialogflowTestTrait
{
    private static $commandFile = __DIR__ . '/../dialogflow.php';

    use TestTrait, ExecuteCommandTrait {
        ExecuteCommandTrait::runCommand as traitRunCommand;
    }

    /** @before */
    public function setupDialogFlowTest()
    {
        $this->useResourceExhaustedBackoff(6);
    }

    private function runCommand($commandName, array $args = [])
    {
        // run in exponential backoff in case of Resource Exhausted errors.
        return $this->traitRunCommand($commandName, $args += [
            'project-id' => self::$projectId
        ]);
    }
}
