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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/monitoring/README.md
 */

namespace Google\Cloud\Samples\Monitoring;

// [START monitoring_write_timeseries]
use Google\Api\Metric;
use Google\Api\MonitoredResource;
use Google\Cloud\Monitoring\V3\MetricServiceClient;
use Google\Cloud\Monitoring\V3\Point;
use Google\Cloud\Monitoring\V3\TimeInterval;
use Google\Cloud\Monitoring\V3\TimeSeries;
use Google\Cloud\Monitoring\V3\TypedValue;
use Google\Protobuf\Timestamp;

/**
 * Example:
 * ```
 * write_timeseries($projectId);
 * ```
 *
 * @param string $projectId Your project ID
 */
function write_timeseries($projectId)
{
    $metrics = new MetricServiceClient([
        'projectId' => $projectId,
    ]);

    $projectName = $metrics->projectName($projectId);

    $endTime = new Timestamp();
    $endTime->setSeconds(time());
    $interval = new TimeInterval();
    $interval->setEndTime($endTime);

    $value = new TypedValue();
    $value->setDoubleValue(123.45);

    $point = new Point();
    $point->setValue($value);
    $point->setInterval($interval);
    $points = [$point];

    $metric = new Metric();
    $metric->setType('custom.googleapis.com/stores/daily_sales');
    $labels = ['store_id' => 'Pittsburg'];
    $metric->setLabels($labels);

    $resource = new MonitoredResource();
    $resource->setType('global');
    $labels = ['project_id' => $projectId];
    $resource->setLabels($labels);

    $timeSeries = new TimeSeries();
    $timeSeries->setMetric($metric);
    $timeSeries->setResource($resource);
    $timeSeries->setPoints($points);

    $result = $metrics->createTimeSeries(
        $projectName,
        [$timeSeries]);

    printf('Done writing time series data.' . PHP_EOL);
}
// [END monitoring_write_timeseries]
