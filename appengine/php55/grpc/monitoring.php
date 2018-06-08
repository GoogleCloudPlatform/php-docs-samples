<?php

require_once __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Api\Metric;
use Google\Api\MonitoredResource;
use Google\Cloud\Monitoring\V3\MetricServiceClient;
use Google\Monitoring\V3\Point;
use Google\Monitoring\V3\TimeInterval;
use Google\Monitoring\V3\TimeSeries;
use Google\Monitoring\V3\TypedValue;
use Google\Protobuf\Timestamp;
# Imports the App Engine SDK
use google\appengine\api\modules\ModulesService;
use google\appengine\api\app_identity\AppIdentityService;

// These variables are set by the App Engine environment. To test locally,
// ensure these are set or manually change their values.
$projectId = AppIdentityService::getApplicationId();
$instanceId = ModulesService::getCurrentInstanceId();
$zone = 'us-central1-f';

$client = new MetricServiceClient();
$projectName = $client->projectName($projectId);
$labels = [
    'instance_id' =>$instanceId,
    'zone' => $zone,
];
$m = new Metric();
$m->setType('custom.googleapis.com/my_metric');
$r = new MonitoredResource();
$r->setType('gce_instance');
$r->setLabels($labels);
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
$points = [$point];
$timeSeries = new TimeSeries();
$timeSeries->setMetric($m);
$timeSeries->setResource($r);
$timeSeries->setPoints($points);
$client->createTimeSeries($projectName, [$timeSeries]);
print('Successfully submitted a time series' . PHP_EOL);
