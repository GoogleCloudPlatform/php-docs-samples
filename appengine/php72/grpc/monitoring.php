<?php
/**
 * Copyright 2018 Google Inc.
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

# Includes the autoloader for libraries installed with composer
require_once __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Api\Metric;
use Google\Api\MonitoredResource;
use Google\Cloud\Monitoring\V3\MetricServiceClient;
use Google\Cloud\Monitoring\V3\Point;
use Google\Cloud\Monitoring\V3\TimeInterval;
use Google\Cloud\Monitoring\V3\TimeSeries;
use Google\Cloud\Monitoring\V3\TypedValue;
use Google\Protobuf\Timestamp;

// These variables are set by the App Engine environment. To test locally,
// ensure these are set or manually change their values.
$projectId = getenv('GOOGLE_CLOUD_PROJECT');
$instanceId = getenv('GAE_INSTANCE');

$client = new MetricServiceClient();

$m = new Metric();
$m->setType('custom.googleapis.com/my_metric');

$r = new MonitoredResource();
$r->setType('gce_instance');
$r->setLabels([
    'instance_id' =>$instanceId,
    'zone' => 'us-central1-f',
]);

$value = new TypedValue();
$value->setDoubleValue(3.14);

$timestamp = new Timestamp();
$timestamp->setSeconds(time());

$interval = new TimeInterval();
$interval->setStartTime($timestamp);
$interval->setEndTime($timestamp);

$point = new Point();
$point->setValue($value);
$point->setInterval($interval);

$timeSeries = new TimeSeries();
$timeSeries->setMetric($m);
$timeSeries->setResource($r);
$timeSeries->setPoints([$point]);

$projectName = $client->projectName($projectId);
$client->createTimeSeries($projectName, [$timeSeries]);
print('Successfully submitted a time series' . PHP_EOL);
