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
 * Google Analytics Data API sample application retrieving dimension and metrics
 * metadata.
 * See https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/getMetadata
 * for more information.
 * Usage:
 *   composer update
 *   php get_metadata_by_property_id.php
 */

namespace Google\Cloud\Samples\Analytics\Data;

// [START analyticsdata_get_metadata_by_property_id]
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\GetMetadataRequest;
use Google\Analytics\Data\V1beta\Metadata;
use Google\ApiCore\ApiException;

/**
 * Retrieves dimensions and metrics available for a Google Analytics 4
 * property, including custom fields.
 * @param string $propertyId Your GA-4 Property ID
 */
function get_metadata_by_property_id(string $propertyId)
{
    // Create an instance of the Google Analytics Data API client library.
    $client = new BetaAnalyticsDataClient();

    $formattedName = sprintf('properties/%s/metadata', $propertyId);

    // Make an API call.
    try {
        $request = (new GetMetadataRequest())
            ->setName($formattedName);
        $response = $client->getMetadata($request);
    } catch (ApiException $ex) {
        printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
        return;
    }

    printf(
        'Dimensions and metrics available for Google Analytics 4 property'
            . ' %s (including custom fields):' . PHP_EOL,
        $propertyId
    );
    printGetMetadataByPropertyId($response);
}

/**
 * Print results of a getMetadata call.
 * @param Metadata $response
 */
function printGetMetadataByPropertyId(Metadata $response)
{
    // [START analyticsdata_print_get_metadata_response]
    foreach ($response->getDimensions() as $dimension) {
        print('DIMENSION' . PHP_EOL);
        printf(
            '%s (%s): %s' . PHP_EOL,
            $dimension->getApiName(),
            $dimension->getUiName(),
            $dimension->getDescription(),
        );
        printf(
            'custom definition: %s' . PHP_EOL,
            $dimension->getCustomDefinition() ? 'true' : 'false'
        );
        if ($dimension->getDeprecatedApiNames()->count() > 0) {
            print('Deprecated API names: ');
            foreach ($dimension->getDeprecatedApiNames() as $name) {
                print($name . ',');
            }
            print(PHP_EOL);
        }
        print(PHP_EOL);
    }

    foreach ($response->getMetrics() as $metric) {
        print('METRIC' . PHP_EOL);
        printf(
            '%s (%s): %s' . PHP_EOL,
            $metric->getApiName(),
            $metric->getUiName(),
            $metric->getDescription(),
        );
        printf(
            'custom definition: %s' . PHP_EOL,
            $metric->getCustomDefinition() ? 'true' : 'false'
        );
        if ($metric->getDeprecatedApiNames()->count() > 0) {
            print('Deprecated API names: ');
            foreach ($metric->getDeprecatedApiNames() as $name) {
                print($name . ',');
            }
            print(PHP_EOL);
        }
        print(PHP_EOL);
    }
    // [END analyticsdata_print_get_metadata_response]
}
// [END analyticsdata_get_metadata_by_property_id]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
