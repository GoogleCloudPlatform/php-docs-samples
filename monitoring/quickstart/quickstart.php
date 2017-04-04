<?php
/**
 * Copyright 2017 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

# [START monitoring_quickstart]
# Includes the autoloader for libraries installed with composer
require_once __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Monitoring\V3\MetricServiceClient;
use google\api\Metric;
use google\api\MonitoredResource;
use google\api\MonitoredResource\LabelsEntry;
use google\monitoring\v3\Point;
use google\monitoring\v3\TimeInterval;
use google\monitoring\v3\TimeSeries;
use google\monitoring\v3\TypedValue;
use google\protobuf\Timestamp;

try {
    $client = new MetricServiceClient();

    $projectId = 'YOUR_PROJECT_ID';

    $formattedProjectName = MetricServiceClient::formatProjectName($projectId);

    $instanceIdLabel = new LabelsEntry();
    $instanceIdLabel->setKey('instance_id')
        ->setValue('1234567890123456789');
    $zoneLabel = new LabelsEntry();
    $zoneLabel->setKey('zone');
    $zoneLabel->setValue('us-central1-f');
    $r = new MonitoredResource();
    $r->setType('gce_instance')
        ->addLabels($instanceIdLabel)
        ->addLabels($zoneLabel);
    $m = new Metric();
    $m->setType('custom.googleapis.com/my_metric');
    $point = new Point();
    $value = new TypedValue();
    $value->setDoubleValue(3.14);
    $interval = new TimeInterval();
    $timestamp = new Timestamp();
    $timestamp->setSeconds(time());
    $interval->setStartTime($timestamp);
    $interval->setEndTime($timestamp);
    $point->setValue($value)
        ->setInterval($interval);
    $timeSeries = new TimeSeries();
    $timeSeries->setMetric($m)
        ->setResource($r)
        ->setPoints($point);
    $client->createTimeSeries($formattedProjectName, [$timeSeries]);
    echo 'Successfully submitted a time series' . PHP_EOL;
} finally {
    $client->close();
}
# [END monitoring_quickstart]
