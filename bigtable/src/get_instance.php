<?php
/**
 * Copyright 2021 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigtable/README.md
 */

namespace Google\Cloud\Samples\Bigtable;

// [START bigtable_get_instance]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type;
use Google\Cloud\Bigtable\Admin\V2\Instance\State;
use Google\ApiCore\ApiException;

/**
 * Get a Bigtable instance
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 */
function get_instance(
    string $projectId,
    string $instanceId
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);

    printf('Fetching the Instance %s' . PHP_EOL, $instanceId);
    try {
        $instance = $instanceAdminClient->getInstance($instanceName);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Instance %s does not exists.' . PHP_EOL, $instanceId);
            return;
        }
        throw $e;
    }

    printf('Printing Details:' . PHP_EOL);

    // Fetch some commonly used metadata
    printf('Name: ' . $instance->getName() . PHP_EOL);
    printf('Display Name: ' . $instance->getDisplayName() . PHP_EOL);
    printf('State: ' . State::name($instance->getState()) . PHP_EOL);
    printf('Type: ' . Type::name($instance->getType()) . PHP_EOL);
    printf('Labels: ' . PHP_EOL);

    $labels = $instance->getLabels();

    // Labels are an object of the MapField class which implement the IteratorAggregate, Countable
    // and ArrayAccess interfaces so you can do the following:
    printf("\tNum of Labels: " . $labels->count() . PHP_EOL);
    printf("\tLabel with a key(dev-label): " . ($labels->offsetExists('dev-label') ? $labels['dev-label'] : 'N/A') . PHP_EOL);

    // we can even loop over all the labels
    foreach ($labels as $key => $val) {
        printf("\t$key: $val" . PHP_EOL);
    }
}
// [END bigtable_get_instance]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
