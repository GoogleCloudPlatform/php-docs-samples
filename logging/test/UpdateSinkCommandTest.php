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

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Samples\Logging\CreateSinkCommand;
use Google\Cloud\Samples\Logging\DeleteSinkCommand;
use Google\Cloud\Samples\Logging\UpdateSinkCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Functional tests for UpdateSinkCommand.
 */
class UpdateSinkCommandTest extends \PHPUnit_Framework_TestCase
{
    /* @var $hasCredentials boolean */
    protected static $hasCredentials;
    /* @var $sinkName string */
    protected static $sinkName;
    /* @var $projectId mixed|string */
    private $projectId;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
        self::$sinkName = sprintf("sink-%s", uniqid());
    }

    public function setUp()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        if (!$this->projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        if (!$bucket = getenv('GOOGLE_BUCKET_NAME')) {
            $this->markTestSkipped('No GOOGLE_BUCKET_NAME envvar');
        }
        $application = new Application();
        $application->add(new CreateSinkCommand());
        $commandTester = new CommandTester($application->get('create-sink'));
        $loggerName = 'my_test_logger';
        $commandTester->execute(
            [
                '--project' => $this->projectId,
                '--logger' => $loggerName,
                '--bucket' => $bucket,
                '--sink' => self::$sinkName,
            ],
            ['interactive' => false]
        );
    }

    public function tearDown()
    {
        // Clean up the sink after the test
        $application = new Application();
        $application->add(new DeleteSinkCommand());
        $commandTester = new CommandTester($application->get('delete-sink'));
        $commandTester->execute(
            [
                '--project' => $this->projectId,
                '--sink' => self::$sinkName,
            ],
            ['interactive' => false]
        );
    }

    public function testUpdateSink()
    {
        $application = new Application();
        $application->add(new UpdateSinkCommand());
        $commandTester = new CommandTester($application->get('update-sink'));
        $commandTester->execute(
            [
                '--project' => $this->projectId,
                '--sink' => self::$sinkName,
                '--logger' => 'updated-logger',
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex(
            sprintf("/Updated a sink '%s'./", self::$sinkName)
        );
        // Check the updated filter value
        $logging = new LoggingClient(['projectId' => $this->projectId]);
        $sink = $logging->sink(self::$sinkName);
        $sink->reload();
        $this->assertRegExp(
            sprintf(
                '|projects/%s/logs/%s|',
                $this->projectId,
                'updated-logger'
            ),
            $sink->info['filter']);
    }

    public function testUpdateSinkWithFilter()
    {
        $application = new Application();
        $application->add(new UpdateSinkCommand());
        $commandTester = new CommandTester($application->get('update-sink'));
        $commandTester->execute(
            [
                '--project' => $this->projectId,
                '--sink' => self::$sinkName,
                '--filter' => 'severity >= INFO',
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex(
            sprintf("/Updated a sink '%s'./", self::$sinkName)
        );
        // Check the updated filter value
        $logging = new LoggingClient(['projectId' => $this->projectId]);
        $sink = $logging->sink(self::$sinkName);
        $sink->reload();
        $this->assertRegExp('/severity >= INFO/', $sink->info['filter']);
    }
}
