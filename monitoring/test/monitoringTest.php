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
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class monitoringTest extends TestCase
{
    const RETRY_COUNT = 5;

    use ExecuteCommandTrait;
    use EventuallyConsistentTestTrait;
    use TestTrait;

    private static $commandFile = __DIR__ . '/../monitoring.php';
    private static $metricId = 'custom.googleapis.com/stores/daily_sales';
    private static $uptimeConfigName;
    private static $minutesAgo = 720;

    // Make retry function longer because creating a metric takes a while
    private function retrySleepFunc($attempts)
    {
        sleep(pow(2, $attempts+2));
    }

    public function testCreateMetric()
    {
        $output = $this->runCommand('create-metric', [
            'project_id' => self::$projectId,
        ]);
        $this->assertContains('Created a metric', $output);
        $this->assertContains(self::$metricId, $output);

        // ensure the metric gets created
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('get-descriptor', [
                'project_id' => self::$projectId,
                'metric_id' => self::$metricId,
            ]);
            $this->assertContains(self::$metricId, $output);
        }, self::RETRY_COUNT, true);
    }

    public function testCreateUptimeCheck()
    {
        $output = $this->runCommand('create-uptime-check', [
            'project_id' => self::$projectId,
        ]);
        $this->assertContains('Created an uptime check', $output);

        $matched = preg_match('/Created an uptime check: (.*)/', $output, $matches);
        $this->assertTrue((bool) $matched);
        self::$uptimeConfigName = $matches[1];
    }

    /** @depends testCreateUptimeCheck */
    public function testGetUptimeCheck()
    {
        $this->runEventuallyConsistentTest(function () {
            $escapedName = addcslashes(self::$uptimeConfigName, '/');
            $output = $this->runCommand('get-uptime-check', [
                'project_id' => self::$projectId,
                'config_name' => self::$uptimeConfigName,
            ]);
            $this->assertContains($escapedName, $output);
        }, self::RETRY_COUNT, true);
    }

    /** @depends testGetUptimeCheck */
    public function testListUptimeChecks()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('list-uptime-checks', [
                'project_id' => self::$projectId,
            ]);
            $this->assertContains(self::$uptimeConfigName, $output);
        });
    }

    /** @depends testCreateUptimeCheck */
    public function testDeleteUptimeCheck()
    {
        $output = $this->runCommand('delete-uptime-check', [
            'project_id' => self::$projectId,
            'config_name' => self::$uptimeConfigName,
        ]);
        $this->assertContains('Deleted an uptime check', $output);
        $this->assertContains(self::$uptimeConfigName, $output);
    }

    public function testListUptimeCheckIPs()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('list-uptime-check-ips', [
                'project_id' => self::$projectId,
            ]);
            $this->assertContains('ip address: ', $output);
        });
    }

    /** @depends testCreateMetric */
    public function testGetDescriptor()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('get-descriptor', [
                'project_id' => self::$projectId,
                'metric_id' => self::$metricId,
            ]);
            $this->assertContains(self::$metricId, $output);
        }, self::RETRY_COUNT, true);
    }

    /** @depends testGetDescriptor */
    public function testListDescriptors()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('list-descriptors', [
                'project_id' => self::$projectId,
            ]);
            $this->assertContains(self::$metricId, $output);
        });
    }

    /** @depends testListDescriptors */
    public function testDeleteMetric()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('delete-metric', [
                'project_id' => self::$projectId,
                'metric_id' => self::$metricId,
            ]);
            $this->assertContains('Deleted a metric', $output);
            $this->assertContains(self::$metricId, $output);
        }, self::RETRY_COUNT, true);
    }

    public function testGetResource()
    {
        $output = $this->runCommand('get-resource', [
            'project_id' => self::$projectId,
            'resource_type' => 'gcs_bucket',
        ]);
        $this->assertContains('A Google Cloud Storage (GCS) bucket.', $output);
    }

    public function testListResources()
    {
        $output = $this->runCommand('list-resources', [
            'project_id' => self::$projectId,
        ]);
        $this->assertContains('gcs_bucket', $output);
    }

    public function testWriteTimeseries()
    {
        // Catch all exceptions as this method occasionally throws an Internal error.
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('write-timeseries', [
                'project_id' => self::$projectId,
            ]);
            $this->assertContains('Done writing time series data', $output);
        }, self::RETRY_COUNT, true);
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesAlign()
    {
        $output = $this->runCommand('read-timeseries-align', [
            'project_id' => self::$projectId,
            '--minutes-ago' => self::$minutesAgo
        ]);
        $this->assertContains('Now', $output);
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesFields()
    {
        $output = $this->runCommand('read-timeseries-fields', [
            'project_id' => self::$projectId,
            '--minutes-ago' => self::$minutesAgo
        ]);
        $this->assertContains('Found data points', $output);
        $this->assertGreaterThanOrEqual(2, substr_count($output, "\n"));
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesReduce()
    {
        $output = $this->runCommand('read-timeseries-reduce', [
            'project_id' => self::$projectId,
            '--minutes-ago' => self::$minutesAgo
        ]);
        $this->assertContains('Last 10 minutes', $output);
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesSimple()
    {
        $output = $this->runCommand('read-timeseries-simple', [
            'project_id' => self::$projectId,
            '--minutes-ago' => self::$minutesAgo
        ]);
        $this->assertContains('CPU utilization:', $output);
        $this->assertGreaterThanOrEqual(2, substr_count($output, "\n"));
    }
}
