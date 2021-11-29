<?php
/*
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/recaptcha/README.md
 */

namespace Google\Cloud\Samples\Recaptcha;

// [START recaptcha_enterprise_delete_site_key]
use Google\ApiCore\ApiException;
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;

/**
 * Delete an existing reCAPTCHA key from your Google Cloud project
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $keyId The 40 char long key ID you wish to delete
 */
function delete_key(string $projectId, string $keyId): void
{
    $client = new RecaptchaEnterpriseServiceClient();
    $formattedKeyName = $client->keyName($projectId, $keyId);

    try {
        $client->deleteKey($formattedKeyName);
        printf('The key: %s is deleted.' . PHP_EOL, $keyId);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('The key with Key ID: %s doesn\'t exist.' . PHP_EOL, $keyId);
        } else {
            print('deleteKey() call failed with the following error: ');
            print($e->getBasicMessage() . PHP_EOL);
        }
    }
}
// [END recaptcha_enterprise_delete_site_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
