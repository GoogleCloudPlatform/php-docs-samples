<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Logging\Tests;

use Google\Cloud\Samples\Logging\DeleteLogger;
use Google\Cloud\Samples\Logging\ListEntries;
use Google\Cloud\Samples\Logging\Write;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Functional tests for DeleteLogger.
 */
class DeleteLoggerTest extends \PHPUnit_Framework_TestCase
{
    /* @var $hasCredentials boolean */
    protected static $hasCredentials;
    /* @var $application \Symfony\Component\Console\Application */
    private $application;
    /* @var $projectId mixed|string */
    private $projectId;
    /* @var $loggerName string */
    private $loggerName;
    /* @var $message string */
    private $message;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        if (!$this->projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        $this->application = new Application();
        $this->application->add(new DeleteLogger());
        $this->application->add(new ListEntries());
        $this->application->add(new Write());
        $this->loggerName = 'my_test_logger';
        $this->message = 'Test Message';
        $commandTester = new CommandTester($this->application->get('write'));
        $commandTester->execute(
            [
                '--project' => $this->projectId,
                '--logger' => $this->loggerName,
                'message' => $this->message
            ],
            ['interactive' => false]
        );
        sleep(2);
    }

    public function testDeleteLogger()
    {
        $commandTester = new CommandTester(
            $this->application->get('delete-logger')
        );
        $commandTester->execute(
            ['--project' => $this->projectId, '--logger' => $this->loggerName],
            ['interactive' => false]
        );
        $this->expectOutputRegex(
            sprintf("/Deleted a logger '%s'./", $this->loggerName)
        );
    }
}
