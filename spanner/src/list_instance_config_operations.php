<?php
/**
 * Copyright 2022 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_list_instance_config_operations]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Lists the instance configuration operations for a project.
 * Example:
 * ```
 * list_instance_config_operations();
 * ```
 */
function list_instance_config_operations()
{
    $spanner = new SpannerClient();

    $operations = $spanner->instanceConfigOperations();
    foreach ($operations as $operation) {
        $meta = $operation->info()['metadata'];
        $instanceConfig = $meta['instanceConfig'];
        $configName = basename($instanceConfig['name']);
        $type = $meta['typeUrl'];
        printf(
            'Instance config operation for %s of type %s has status %s.' . PHP_EOL,
            $configName,
            $type,
            $operation->done() ? 'done' : 'running'
        );
    }
}
// [END spanner_list_instance_config_operations]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
