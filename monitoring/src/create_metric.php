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

// [START monitoring_create_metric]
use Google\Cloud\Monitoring\V3\MetricServiceClient;
use Google\Api\LabelDescriptor;
use Google\Api\LabelDescriptor_ValueType;
use Google\Api\MetricDescriptor;
use Google\Api\MetricDescriptor_MetricKind;
use Google\Api\MetricDescriptor_ValueType;

/**
 * Create a new metric in Stackdriver Monitoring.
 * Example:
 * ```
 * create_metric($projectId);
 * ```
 *
 * @param string $projectId Your project ID
 */
function create_metric($projectId)
{
    $metrics = new MetricServiceClient([
        'projectId' => $projectId,
    ]);

    $projectName = $metrics->projectName($projectId);

    $descriptor = new MetricDescriptor();
    $descriptor->setDescription('Daily sales records from all branch stores.');
    $descriptor->setDisplayName('Daily Sales');
    $descriptor->setType('custom.googleapis.com/stores/daily_sales');
    $descriptor->setMetricKind(MetricDescriptor_MetricKind::GAUGE);
    $descriptor->setValueType(MetricDescriptor_ValueType::DOUBLE);
    $descriptor->setUnit('{USD}');
    $label = new LabelDescriptor();
    $label->setKey('store_id');
    $label->setValueType(LabelDescriptor_ValueType::STRING);
    $label->setDescription('The ID of the store.');
    $labels = [$label];
    $descriptor->setLabels($labels);

    $descriptor = $metrics->createMetricDescriptor($projectName, $descriptor);
    printf('Created a metric: ' . $descriptor->getName() . PHP_EOL);
}
// [END monitoring_create_metric]
