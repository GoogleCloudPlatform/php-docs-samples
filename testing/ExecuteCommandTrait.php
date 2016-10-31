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

namespace Google\Cloud\TestUtils;

use Symfony\Component\Process\Process;

trait ExecuteCommandTrait
{
    private static $workingDirectory;
    private static $logger;

    public static function setWorkingDirectory($workingDirectory)
    {
        self::$workingDirectory = $workingDirectory;
    }

    /**
     * run a command
     *
     * @param $cmd
     * @throws \Exception
     */
    public static function execute($cmd, $timeout = false)
    {
        $process = self::createProcess($cmd, $timeout);
        self::executeProcess($process);

        return $process->getOutput();
    }

    /**
     * Executes a Process and throws an exception
     *
     * @param Process $process
     * @param bool $throwExceptionOnFailure
     * @throws \Exception
     */
    private static function executeProcess(Process $process, $throwExceptionOnFailure = true)
    {
        if (self::$logger) {
            self::$logger->debug(sprintf('Executing: %s', $process->getCommandLine()));
        }

        $process->run(self::getCallback());

        if (!$process->isSuccessful() && $throwExceptionOnFailure) {
            $output = $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
            $msg = sprintf('Error executing "%s": %s', $process->getCommandLine(), $output);

            throw new \Exception($msg);
        }

        return $process->isSuccessful();
    }

    /**
     * @return Process
     */
    private static function createProcess($cmd, $timeout = false)
    {
        $process = new Process($cmd);
        $process->setWorkingDirectory(self::$workingDirectory);
        if (false !== $timeout) {
            $process->setTimeout($timeout);
        }

        return $process;
    }

    private static function getCallback()
    {
        if (self::$logger) {
            $logger = self::$logger;
            return function ($type, $line) use ($logger) {
                if ($type === 'err') {
                    $logger->error($line);
                } else {
                    $logger->debug($line);
                }
            };
        }
    }

    protected static function executeWithRetry($cmd, $timeout = false, $retries = 3)
    {
        $process = self::createProcess($cmd, $timeout);
        for ($i = 0; $i <= $retries; $i++) {
            // only allow throwing exceptions on final attempt
            $throwExceptionOnFailure = $i == $retries;
            if (self::executeProcess($process, $throwExceptionOnFailure)) {
                return true;
            }
            if (self::$logger && $i < $retries) {
                self::$logger->debug('Retrying the command: ' . $cmd);
            }
        }
        return false;
    }
}
