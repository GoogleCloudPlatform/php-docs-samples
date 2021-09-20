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

// [START recaptcha_enterprise_get_site_key]
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings\IntegrationType;
use Google\ApiCore\ApiException;

/**
 * Get a reCAPTCHA key from a google cloud project
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $keyId The 40 char long key ID you wish to fetch
 */
function get_key(string $projectId, string $keyId): void
{
    $client = new RecaptchaEnterpriseServiceClient();
    $formattedKeyName = $client->keyName($projectId, $keyId);

    try {
        // Returns a 'Google\Cloud\RecaptchaEnterprise\V1\Key' object
        $key = $client->getKey($formattedKeyName);
        $webSettings = $key->getWebSettings();

        print('Key fetched' . PHP_EOL);
        printf('Display name: %s' . PHP_EOL, $key->getDisplayName());
        // $key->getCreateTime() returns a Google\Protobuf\Timestamp object
        printf('Create time: %d' . PHP_EOL, $key->getCreateTime()->getSeconds());
        printf('Web platform settings: %s' . PHP_EOL, $key->hasWebSettings() ? 'Yes' : 'No');
        printf('Allowed all domains: %s' . PHP_EOL, $key->hasWebSettings() && $webSettings->getAllowAllDomains() ? 'Yes' : 'No');
        printf('Integration Type: %s' . PHP_EOL, $key->hasWebSettings() ? IntegrationType::name($webSettings->getIntegrationType()) : 'N/A');
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('The key with Key ID: %s doesn\'t exist.' . PHP_EOL, $keyId);
        } else {
            print('getKey() call failed with the following error: ');
            print($e);
        }
    }
}
// [END recaptcha_enterprise_get_site_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
