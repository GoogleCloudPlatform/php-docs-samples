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
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class monitoringTest extends TestCase
{
    const RETRY_COUNT = 5;

    use EventuallyConsistentTestTrait;
    use TestTrait;

    private static $metricId = 'custom.googleapis.com/stores/daily_sales';
    private static $uptimeConfigName;
    private static $minutesAgo = 720;

    // Make retry function longer because creating a metric takes a while
    private function retrySleepFunc($attempts)
    {
        sleep(pow(2, $attempts + 2));
    }

    public function testCreateMetric()
    {
        $output = $this->runFunctionSnippet('create_metric', [
            'projectId' => self::$projectId,
        ]);
        $this->assertStringContainsString('Created a metric', $output);
        $this->assertStringContainsString(self::$metricId, $output);

        // ensure the metric gets created
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('get_descriptor', [
                'projectId' => self::$projectId,
                'metricId' => self::$metricId,
            ]);
            $this->assertStringContainsString(self::$metricId, $output);
        }, self::RETRY_COUNT, true);
    }

    public function testCreateUptimeCheck()
    {
        $output = $this->runFunctionSnippet('create_uptime_check', [
            'projectId' => self::$projectId,
        ]);
        $this->assertStringContainsString('Created an uptime check', $output);

        $matched = preg_match('/Created an uptime check: (.*)/', $output, $matches);
        $this->assertTrue((bool) $matched);
        self::$uptimeConfigName = $matches[1];
    }

    /** @depends testCreateUptimeCheck */
    public function testGetUptimeCheck()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('get_uptime_check', [
                'projectId' => self::$projectId,
                'configName' => self::$uptimeConfigName,
            ]);
            $this->assertStringContainsString(self::$uptimeConfigName, $output);
        }, self::RETRY_COUNT, true);
    }

    /** @depends testGetUptimeCheck */
    public function testListUptimeChecks()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('list_uptime_checks', [
                'projectId' => self::$projectId,
            ]);
            $this->assertStringContainsString(self::$uptimeConfigName, $output);
        });
    }

    /** @depends testCreateUptimeCheck */
    public function testDeleteUptimeCheck()
    {
        $output = $this->runFunctionSnippet('delete_uptime_check', [
            'projectId' => self::$projectId,
            'configName' => self::$uptimeConfigName,
        ]);
        $this->assertStringContainsString('Deleted an uptime check', $output);
        $this->assertStringContainsString(self::$uptimeConfigName, $output);
    }

    public function testListUptimeCheckIPs()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('list_uptime_check_ips', [
                'projectId' => self::$projectId,
            ]);
            $this->assertStringContainsString('ip address: ', $output);
        });
    }

    /** @depends testCreateMetric */
    public function testGetDescriptor()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('get_descriptor', [
                'projectId' => self::$projectId,
                'metricId' => self::$metricId,
            ]);
            $this->assertStringContainsString(self::$metricId, $output);
        }, self::RETRY_COUNT, true);
    }

    /** @depends testGetDescriptor */
    public function testListDescriptors()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('list_descriptors', [
                'projectId' => self::$projectId,
            ]);
            $this->assertStringContainsString(self::$metricId, $output);
        });
    }

    /** @depends testListDescriptors */
    public function testDeleteMetric()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('delete_metric', [
                'projectId' => self::$projectId,
                'metricId' => self::$metricId,
            ]);
            $this->assertStringContainsString('Deleted a metric', $output);
            $this->assertStringContainsString(self::$metricId, $output);
        }, self::RETRY_COUNT, true);
    }

    public function testGetResource()
    {
        $output = $this->runFunctionSnippet('get_resource', [
            'projectId' => self::$projectId,
            'resourceType' => 'gcs_bucket',
        ]);
        $this->assertStringContainsString('A Google Cloud Storage (GCS) bucket.', $output);
    }

    public function testListResources()
    {
        $output = $this->runFunctionSnippet('list_resources', [
            'projectId' => self::$projectId,
        ]);
        $this->assertStringContainsString('gcs_bucket', $output);
    }

    public function testWriteTimeseries()
    {
        // Catch all exceptions as this method occasionally throws an Internal error.
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('write_timeseries', [
                'projectId' => self::$projectId,
            ]);
            $this->assertStringContainsString('Done writing time series data', $output);
        }, self::RETRY_COUNT, true);
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesAlign()
    {
        $output = $this->runFunctionSnippet('read_timeseries_align', [
            'projectId' => self::$projectId,
            'minutesAgo' => self::$minutesAgo
        ]);
        $this->assertStringContainsString('Now', $output);
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesFields()
    {
        $output = $this->runFunctionSnippet('read_timeseries_fields', [
            'projectId' => self::$projectId,
            'minutesAgo' => self::$minutesAgo
        ]);
        $this->assertStringContainsString('Found data points', $output);
        $this->assertGreaterThanOrEqual(2, substr_count($output, "\n"));
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesReduce()
    {
        $output = $this->runFunctionSnippet('read_timeseries_reduce', [
            'projectId' => self::$projectId,
            'minutesAgo' => self::$minutesAgo
        ]);
        $this->assertStringContainsString('Last 10 minutes', $output);
    }

    /** @depends testWriteTimeseries */
    public function testReadTimeseriesSimple()
    {
        $output = $this->runFunctionSnippet('read_timeseries_simple', [
            'projectId' => self::$projectId,
            'minutesAgo' => self::$minutesAgo
        ]);
        $this->assertStringContainsString('CPU utilization:', $output);
        $this->assertGreaterThanOrEqual(2, substr_count($output, "\n"));
    }
}
