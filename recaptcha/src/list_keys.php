<?php
/*
 * Copyright 2020 Google LLC.
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

// [START recaptcha_enterprise_list_site_keys]
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;

/**
 * List all the reCAPTCHA keys associate to a Google Cloud project
 * @param string $projectId Your Google Cloud project ID
 */
function list_keys(
    string $projectId
): void {
    $client = new RecaptchaEnterpriseServiceClient();
    $formattedProject = $client->projectName($projectId);

    try {
        $response = $client->listKeys(
            $formattedProject,
            [
                'pageSize'=>2
            ]
        );

        printf('Keys fetched' . PHP_EOL);

        // either fetch the first page
        foreach ($response->getPage() as $key) {
            printf($key->getDisplayName() . PHP_EOL);
        }

        // or iterate over all the keys and let the library handle the paging
        // foreach($response->iterateAllElements() as $key){
        //     printf($key->getDisplayName() . PHP_EOL);
        // }
    } catch (exception $e) {
        printf('listKeys() call failed with the following error: ');
        printf($e);
    }
}
// [END recaptcha_enterprise_list_site_keys]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
