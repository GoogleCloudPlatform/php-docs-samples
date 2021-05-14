<?php
/**
 * Copyright 2021 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/compute/cloud-client/README.md
 */

namespace Google\Cloud\Samples\Compute;

# [START compute_instances_operation_check]
use Google\Cloud\Compute\V1\Operation;
use Google\Cloud\Compute\V1\GlobalOperationsClient;
use Google\Cloud\Compute\V1\RegionOperationsClient;
use Google\Cloud\Compute\V1\ZoneOperationsClient;

/**
 * Wait for operation to finish.
 * Example:
 * ```
 * wait_for_operation($operation, $projectId);
 * ```
 *
 * @param Operation $operation Your operation object.
 * @param string $projectId Your Google Cloud project ID.
 * @param int $timeoutMillis Wait timeout in ms (default: 120000 ms = 2 minutes)
 * @return Operation
 *
 * @throws \Google\ApiCore\ApiException
 */
function wait_for_operation(
    Operation $operation,
    string $projectId,
    int $timeoutMillis = 120000
) {
    $optionalArgs = array('timeoutMillis' => $timeoutMillis);

    if ($operation->hasZone()) {
        $client = new ZoneOperationsClient();

        # $operation->getZone() is a full URL address of a zone, so we need to extract just the name
        $zoneArr = explode('/', $operation->getZone());
        $zone = array_pop($zoneArr);

        $operation = $client->wait($operation->getName(), $projectId, $zone, $optionalArgs);
    } elseif ($operation->hasRegion()) {
        $client = new RegionOperationsClient();

        # $operation->getRegion() is a full URL address of a zone, so we need to extract just the name
        $regionArr = explode('/', $operation->getRegion());
        $region = array_pop($regionArr);

        $operation = $client->wait($operation->getName(), $projectId, $region, $optionalArgs);
    } else {
        $client = new GlobalOperationsClient();
        $operation = $client->wait($operation->getName(), $projectId, $optionalArgs);
    }

    if ($operation->hasError()) {
        printf('Error occurred %s' . PHP_EOL, $operation->getError());
        throw new \Google\ApiCore\ApiException($operation->getError());
    } elseif (count($operation->getWarnings()) > 0) {
        foreach ($operation->getWarnings() as $warning) {
            printf('Warning occurred %s' . PHP_EOL, $warning->getMessage());
        }
    }

    return $operation;
}
# [END compute_instances_operation_check]
