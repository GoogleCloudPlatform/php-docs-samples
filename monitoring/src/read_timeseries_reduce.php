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

// [START monitoring_read_timeseries_reduce]
use Google\Cloud\Monitoring\V3\MetricServiceClient;
use Google\Cloud\Monitoring\V3\Aggregation_Aligner;
use Google\Cloud\Monitoring\V3\Aggregation_Reducer;
use Google\Cloud\Monitoring\V3\Aggregation;
use Google\Cloud\Monitoring\V3\TimeInterval;
use Google\Cloud\Monitoring\V3\ListTimeSeriesRequest_TimeSeriesView;
use Google\Protobuf\Duration;
use Google\Protobuf\Timestamp;

/**
 * Example:
 * ```
 * read_timeseries_reduce($projectId);
 * ```
 *
 * @param string $projectId Your project ID
 */
function read_timeseries_reduce($projectId, $minutesAgo = 20)
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
    $aggregation->setCrossSeriesReducer(Aggregation_Reducer::REDUCE_MEAN);
    $aggregation->setPerSeriesAligner(Aggregation_Aligner::ALIGN_MEAN);

    $view = ListTimeSeriesRequest_TimeSeriesView::FULL;

    $result = $metrics->listTimeSeries(
        $projectName,
        $filter,
        $interval,
        $view,
        ['aggregation' => $aggregation]);

    printf('Average CPU utilization across all GCE instances:' . PHP_EOL);
    if ($timeSeries = $result->iterateAllElements()->current()) {
        $reductions = $timeSeries->getPoints();
        printf('  Last 10 minutes: ');
        printf($reductions[0]->getValue()->getDoubleValue() . PHP_EOL);
        if (count($reductions) > 1) {
            printf('  10-20 minutes ago: ');
            printf($reductions[1]->getValue()->getDoubleValue() . PHP_EOL);
        }
    }
}
// [END monitoring_read_timeseries_reduce]
