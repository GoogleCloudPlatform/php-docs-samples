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

namespace Google\Cloud\Samples\Monitoring;

use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Symfony\Component\Console\Tester\CommandTester;

class monitoringTest extends \PHPUnit_Framework_TestCase
{
    const RETRY_COUNT = 5;

    use EventuallyConsistentTestTrait;

    private static $projectId;
    private static $metricId = 'custom.googleapis.com/stores/daily_sales';
    private static $minutesAgo = 720;

    public static function setUpBeforeClass()
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            self::markTestSkipped('No application credentials were found');
        }
        if (!self::$projectId = getenv('GOOGLE_PROJECT_ID')) {
            self::markTestSkipped('GOOGLE_PROJECT_ID must be set.');
        }
    }

    public function testCreateMetric()
    {
        $output = $this->runCommand('create-metric');
        $this->assertContains('Created a metric', $output);
        $this->assertContains(self::$metricId, $output);

        // ensure the metric gets created
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('get-descriptor', [
                'metric_id' => self::$metricId,
            ]);
            $this->assertContains(self::$metricId, $output);
        }, self::RETRY_COUNT, true);
    }

    /** @depends testCreateMetric */
    public function testGetDescriptor()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('get-descriptor', [
                'metric_id' => self::$metricId,
            ]);
            $this->assertContains(self::$metricId, $output);
        }, self::RETRY_COUNT, true);
    }

    /** @depends testGetDescriptor */
    public function testListDescriptors()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('list-descriptors');
            $this->assertContains(self::$metricId, $output);
        });
    }

    /** @depends testListDescriptors */
    public function testDeleteMetric()
    {
        $output = $this->runCommand('delete-metric', [
            'metric_id' => self::$metricId,
        ]);
        $this->assertContains('Deleted a metric', $output);
        $this->assertContains(self::$metricId, $output);
    }

    public function testWriteTimeseries()
    {
        // Catch all exceptions as this method occasionally throws an Internal error.
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('write-timeseries');
            $this->assertContains('Done writing time series data', $output);
        }, self::RETRY_COUNT, true);
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesAlign()
    {
        $output = $this->runCommand('read-timeseries-align', [
            '--minutes-ago' => self::$minutesAgo
        ]);
        $this->assertContains('Now', $output);
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesFields()
    {
        $output = $this->runCommand('read-timeseries-fields', [
            '--minutes-ago' => self::$minutesAgo
        ]);
        $this->assertContains('Found data points', $output);
        $this->assertGreaterThanOrEqual(2, substr_count($output, "\n"));
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesReduce()
    {
        $output = $this->runCommand('read-timeseries-reduce', [
            '--minutes-ago' => self::$minutesAgo
        ]);
        $this->assertContains('Last 10 minutes', $output);
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesSimple()
    {
        $output = $this->runCommand('read-timeseries-simple', [
            '--minutes-ago' => self::$minutesAgo
        ]);
        $this->assertContains('CPU utilization:', $output);
        $this->assertGreaterThanOrEqual(2, substr_count($output, "\n"));
    }

    private function runCommand($commandName, $args = [])
    {
        $application = require __DIR__ . '/../monitoring.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        try {
            $commandTester->execute(
                ['project_id' => self::$projectId] + $args,
                ['interactive' => false]);
        } catch (\Google\GAX\ApiException $e) {
            // if the command throws an error cast it as a string (as this would be the output)
            $application->renderException($e, $commandTester->getOutput());
            return $commandTester->getDisplay();
        } finally {
            $output = ob_get_clean();
        }
        return $output;
    }
}
