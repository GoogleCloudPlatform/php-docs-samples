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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/monitoring/README.md
 */

namespace Google\Cloud\Samples\Monitoring;

# [START monitoring_alert_create_policy]
use Google\Cloud\Monitoring\V3\AlertPolicyServiceClient;
use Google\Cloud\Monitoring\V3\AlertPolicy;
use Google\Cloud\Monitoring\V3\ComparisonType;
use Google\Cloud\Monitoring\V3\AlertPolicy\Condition;
use Google\Cloud\Monitoring\V3\AlertPolicy\Condition\MetricThreshold;
use Google\Cloud\Monitoring\V3\AlertPolicy\ConditionCombinerType;
use Google\Protobuf\Duration;

/**
 * @param string $projectId Your project ID
 */
function alert_create_policy($projectId)
{
    $alertClient = new AlertPolicyServiceClient([
        'projectId' => $projectId,
    ]);
    $projectName = $alertClient->projectName($projectId);

    $policy = new AlertPolicy();
    $policy->setDisplayName('Test Alert Policy');
    $policy->setCombiner(ConditionCombinerType::PBOR);
    /** @see https://cloud.google.com/monitoring/api/resources for a list of resource.type */
    /** @see https://cloud.google.com/monitoring/api/metrics_gcp for a list of metric.type */
    $policy->setConditions([new Condition([
        'display_name' => 'condition-1',
        'condition_threshold' => new MetricThreshold([
            'filter' => 'resource.type = "gce_instance" AND metric.type = "compute.googleapis.com/instance/cpu/utilization"',
            'duration' => new Duration(['seconds' => '60']),
            'comparison' => ComparisonType::COMPARISON_LT,
        ])
    ])]);

    $policy = $alertClient->createAlertPolicy($projectName, $policy);
    printf('Created alert policy %s' . PHP_EOL, $policy->getName());
}
# [END monitoring_alert_create_policy]
