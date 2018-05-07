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

namespace Google\Cloud\Samples\Logging\Tests;

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Functional tests for ListSinkCommand.
 */
class loggingTest extends \PHPUnit_Framework_TestCase
{
    const RETRY_COUNT = 5;

    use EventuallyConsistentTestTrait;

    /* @var $hasCredentials boolean */
    protected static $hasCredentials;
    /* @var $sinkName string */
    protected static $sinkName;
    /* @var $sinkName string */
    protected static $loggerName;
    /* @var $projectId mixed|string */
    private $projectId;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
        self::$loggerName = 'my_test_logger';
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
    }

    public function testCreateSink()
    {
        if (!$bucket = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('Set the GOOGLE_STORAGE_BUCKET environment variable');
        }
        $output = $this->runCommand('create-sink', [
            '--logger' => self::$loggerName,
            '--bucket' => $bucket . '/logging',
            '--sink' => self::$sinkName,
        ]);
        $this->assertEquals(
            sprintf("Created a sink '%s'.\n", self::$sinkName),
            $output
        );
    }

    /**
     * @depends testCreateSink
     */
    public function testListSinks()
    {
        $output = $this->runCommand('list-sinks');
        $this->assertContains('name:' . self::$sinkName, $output);
    }

    /**
     * @depends testCreateSink
     */
    public function testUpdateSink()
    {
        $output = $this->runCommand('update-sink', [
            '--sink' => self::$sinkName,
            '--logger' => 'updated-logger',
        ]);
        $this->assertEquals(
            sprintf("Updated a sink '%s'.\n", self::$sinkName),
            $output
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
            $sink->info()['filter']
        );
    }

    /**
     * @depends testCreateSink
     */
    public function testUpdateSinkWithFilter()
    {
        $output = $this->runCommand('update-sink', [
            '--sink' => self::$sinkName,
            '--filter' => 'severity >= INFO',
        ]);
        $this->assertEquals(
            sprintf("Updated a sink '%s'.\n", self::$sinkName),
            $output
        );
        // Check the updated filter value
        $logging = new LoggingClient(['projectId' => $this->projectId]);
        $sink = $logging->sink(self::$sinkName);
        $sink->reload();
        $this->assertRegExp('/severity >= INFO/', $sink->info()['filter']);
    }

    /**
     * @depends testCreateSink
     */
    public function testDeleteSink()
    {
        $output = $this->runCommand('delete-sink', [
            '--sink' => self::$sinkName,
        ]);
        $this->assertEquals(
            sprintf("Deleted a sink '%s'.\n", self::$sinkName),
            $output
        );
    }

    public function testWriteAndList()
    {
        $message = sprintf("Test Message %s", uniqid());
        $output = $this->runCommand('write', [
            '--logger' => self::$loggerName,
            'message' => $message
        ]);
        $this->assertEquals(
            sprintf("Wrote a log to a logger '%s'.\n", self::$loggerName),
            $output
        );

        $loggerName = self::$loggerName;
        $this->runEventuallyConsistentTest(function () use ($loggerName, $message) {
            $output = $this->runCommand('list-entries', [
                '--logger' => $loggerName,
            ]);
            $this->assertContains($message, $output);
        }, self::RETRY_COUNT, true);
    }

    /**
     * @depends testWriteAndList
     */
    public function testDeleteLogger()
    {
        $output = $this->runCommand('delete-logger', [
            '--logger' => self::$loggerName,
        ]);
        $this->assertEquals(
            sprintf("Deleted a logger '%s'.\n", self::$loggerName),
            $output
        );
    }

    public function testWritePsr()
    {
        $message = 'Test Message';
        $output = $this->runCommand('write-psr', [
            '--logger' => self::$loggerName,
            '--level' => 'emergency',
            'message' => $message,
        ]);
        $this->assertEquals(
            sprintf("Wrote to PSR logger '%s' at level 'emergency'.\n", self::$loggerName),
            $output
        );
    }

    public function testWriteMonolog()
    {
        $message = 'Test Message';
        $output = $this->runCommand('write-monolog', [
            '--logger' => self::$loggerName,
            '--level' => 'emergency',
            'message' => $message,
        ]);
        $this->assertEquals(
            sprintf("Wrote to monolog logger '%s' at level 'emergency'.\n", self::$loggerName),
            $output
        );
    }

    public function runCommand($commandName, $additionalArgs = [])
    {
        $application = require __DIR__ . '/../logging.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute([
            'project' => $this->projectId,
        ] + $additionalArgs, [
            'interactive' => false
        ]);

        return ob_get_clean();
    }
}
