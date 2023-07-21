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

namespace Google\Cloud\Samples\Analytics\Data;

/**
 * This file is to be used as an example only!
 *
 * Usage:
 * ```
 * $credentialsJsonPath = '/path/to/credentials.json';
 * $analyticsDataClient = require 'src/client_from_json_credentials.php';
 * ```
 */
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;

// [START analyticsdata_json_credentials_initialize]
/**
 * @param string $credentialsJsonPath Valid path to the credentials.json file for your service
 *                                    account downloaded from the Cloud Console.
 *                                    Example: "/path/to/credentials.json"
 */
function client_from_json_credentials(string $credentialsJsonPath)
{
    // Explicitly use service account credentials by specifying
    // the private key file.
    $client = new BetaAnalyticsDataClient([
        'credentials' => $credentialsJsonPath
    ]);

    return $client;
}
// [END analyticsdata_json_credentials_initialize]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
