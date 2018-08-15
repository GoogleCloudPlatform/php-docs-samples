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

// [START monitoring_read_timeseries_align]
use Google\Cloud\Monitoring\V3\MetricServiceClient;
use Google\Cloud\Monitoring\V3\Aggregation_Aligner;
use Google\Cloud\Monitoring\V3\Aggregation;
use Google\Cloud\Monitoring\V3\TimeInterval;
use Google\Cloud\Monitoring\V3\ListTimeSeriesRequest_TimeSeriesView;
use Google\Protobuf\Duration;
use Google\Protobuf\Timestamp;

/**
 * Example:
 * ```
 * read_timeseries_align($projectId);
 * ```
 *
 * @param string $projectId Your project ID
 */
function read_timeseries_align($projectId, $minutesAgo = 20)
{
    $metrics = new MetricServiceClient([
        'projectId' => $projectId,
    ]);

    $projectName = $metrics->projectName($projectId);
    $filter = 'metric.type="compute.googleapis.com/instance/cpu/utilization"';

    $startTime = new Timestamp();
    $startTime->setSeconds(time() - (60 * $minutesAgo));
    $endTime = new Timestamp();
    $endTime->setSeconds(time());

    $interval = new TimeInterval();
    $interval->setStartTime($startTime);
    $interval->setEndTime($endTime);

    $alignmentPeriod = new Duration();
    $alignmentPeriod->setSeconds(600);
    $aggregation = new Aggregation();
    $aggregation->setAlignmentPeriod($alignmentPeriod);
    $aggregation->setPerSeriesAligner(Aggregation_Aligner::ALIGN_MEAN);

    $view = ListTimeSeriesRequest_TimeSeriesView::FULL;

    $result = $metrics->listTimeSeries(
        $projectName,
        $filter,
        $interval,
        $view,
        ['aggregation' => $aggregation]);

    printf('CPU utilization:' . PHP_EOL);
    foreach ($result->iterateAllElements() as $timeSeries) {
        printf($timeSeries->getMetric()->getLabels()['instance_name'] . PHP_EOL);
        printf('  Now: ');
        printf($timeSeries->getPoints()[0]->getValue()->getDoubleValue() . PHP_EOL);
        if (count($timeSeries->getPoints()) > 1) {
            printf('  10 minutes ago: ');
            printf($timeSeries->getPoints()[1]->getValue()->getDoubleValue() . PHP_EOL);
        }
    }
}
// [END monitoring_read_timeseries_align]
