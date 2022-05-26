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
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Functional tests for ListSinkCommand.
 */
class loggingTest extends TestCase
{
    use TestTrait;
    use EventuallyConsistentTestTrait;

    protected static $sinkName;
    protected static $loggerName = 'my_test_logger';

    public static function setUpBeforeClass(): void
    {
        self::$sinkName = sprintf('sink-%s', uniqid());
    }

    public function setUp(): void
    {
        $this->useResourceExhaustedBackoff(5);
        $this->catchAllExceptions = true;
    }

    public function testCreateSink()
    {
        $loggerFullName = sprintf('projects/%s/logs/%s', self::$projectId, self::$loggerName);
        $output = $this->runFunctionSnippet('create_sink', [
            'project' => self::$projectId,
            'sinkName' => self::$sinkName,
            'destination' => self::$projectId . '/logging',
            'filterString' => sprintf('logName = "%s"', $loggerFullName),
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
        $output = $this->runFunctionSnippet('list_sinks', [
            'project' => self::$projectId,
        ]);
        $this->assertStringContainsString('name:' . self::$sinkName, $output);
    }

    /**
     * @depends testCreateSink
     */
    public function testUpdateSink()
    {
        $loggerFullName = sprintf('projects/%s/logs/updated-logger', self::$projectId);
        $output = $this->runFunctionSnippet('update_sink', [
            'project' => self::$projectId,
            'sinkName' => self::$sinkName,
            'filterString' => sprintf('logName = "%s"', $loggerFullName),
        ]);
        $this->assertEquals(
            sprintf("Updated a sink '%s'.\n", self::$sinkName),
            $output
        );
        // Check the updated filter value
        $logging = new LoggingClient(['projectId' => self::$projectId]);
        $sink = $logging->sink(self::$sinkName);
        $sink->reload();
        $this->assertRegExp(
            sprintf(
                '|projects/%s/logs/%s|',
                self::$projectId,
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
        $output = $this->runFunctionSnippet('update_sink', [
            'project' => self::$projectId,
            'sinkName' => self::$sinkName,
            'filterString' => 'severity >= INFO',
        ]);
        $this->assertEquals(
            sprintf("Updated a sink '%s'.\n", self::$sinkName),
            $output
        );
        // Check the updated filter value
        $logging = new LoggingClient(['projectId' => self::$projectId]);
        $sink = $logging->sink(self::$sinkName);
        $sink->reload();
        $this->assertRegExp('/severity >= INFO/', $sink->info()['filter']);
    }

    /**
     * @depends testCreateSink
     */
    public function testDeleteSink()
    {
        $output = $this->runFunctionSnippet('delete_sink', [
            'project' => self::$projectId,
            'sinkName' => self::$sinkName,
        ]);
        $this->assertEquals(
            sprintf("Deleted a sink '%s'.\n", self::$sinkName),
            $output
        );
    }

    public function testWriteAndList()
    {
        $message = sprintf('Test Message %s', uniqid());
        $output = $this->runFunctionSnippet('write', [
            'project' => self::$projectId,
            'message' => $message,
            'loggerName' => self::$loggerName,
        ]);
        $this->assertEquals(
            sprintf("Wrote a log to a logger '%s'.\n", self::$loggerName),
            $output
        );

        $loggerName = self::$loggerName;
        $this->runEventuallyConsistentTest(function () use ($loggerName, $message) {
            $output = $this->runFunctionSnippet('list_entries', [
                'project' => self::$projectId,
                'loggerName' => $loggerName,
            ]);
            $this->assertStringContainsString($message, $output);
        }, $retries = 10);
    }

    /**
     * @depends testWriteAndList
     */
    public function testDeleteLogger()
    {
        $output = $this->runFunctionSnippet('delete_logger', [
            'project' => self::$projectId,
            'loggerName' => self::$loggerName,
        ]);
        $this->assertEquals(
            sprintf("Deleted a logger '%s'.\n", self::$loggerName),
            $output
        );
    }

    public function testWritePsr()
    {
        $message = 'Test Message';
        $output = $this->runFunctionSnippet('write_with_psr_logger', [
            'project' => self::$projectId,
            'message' => $message,
            'loggerName' => self::$loggerName,
            'level' => 'emergency',
        ]);
        $this->assertEquals(
            sprintf("Wrote to PSR logger '%s' at level 'emergency'.\n", self::$loggerName),
            $output
        );
    }

    public function testWriteMonolog()
    {
        $message = 'Test Message';
        $output = $this->runFunctionSnippet('write_with_monolog_logger', [
            'project' => self::$projectId,
            'message' => $message,
            'loggerName' => self::$loggerName,
            'level' => 'emergency',
        ]);
        $this->assertEquals(
            sprintf("Wrote to monolog logger '%s' at level 'emergency'.\n", self::$loggerName),
            $output
        );
    }
}
