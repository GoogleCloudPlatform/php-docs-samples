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

// [START recaptcha_enterprise_update_site_key]
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Key;
use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings;
use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings\ChallengeSecurityPreference;
use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings\IntegrationType;
use Google\Protobuf\FieldMask;
use Google\ApiCore\ApiException;

/**
 * Update an existing reCAPTCHA site key
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $keyId The 40 char long key ID you wish to update
 * @param string $updatedName The updated display name of the reCAPTCHA key
 */
function update_key(
    string $projectId,
    string $keyId,
    string $updatedName
): void {
    $client = new RecaptchaEnterpriseServiceClient();
    $formattedKeyName = $client->keyName($projectId, $keyId);

    // Create the settings for the key.
    // In order to create other keys we'll use AndroidKeySettings or IOSKeySettings
    $settings = new WebKeySettings();

    // Allow the key to work for all domains(Not recommended)
    // $settings->setAllowAllDomains(false);
    // ...or explicitly set the allowed domains for the key as an array of strings
    $settings->setAllowedDomains(['google.com']);

    // Specify the type of the key
    // - score based key -> IntegrationType::SCORE
    // - checkbox based key -> IntegrationType::CHECKBOX
    // Read https://cloud.google.com/recaptcha-enterprise/docs/choose-key-type
    $settings->setIntegrationType(IntegrationType::CHECKBOX);

    // Specify the possible challenge frequency and difficulty
    // Read https://cloud.google.com/recaptcha-enterprise/docs/reference/rest/v1/projects.keys#challengesecuritypreference
    $settings->setChallengeSecurityPreference(ChallengeSecurityPreference::SECURITY);

    $key = new Key();
    $key->setName($formattedKeyName);
    $key->setDisplayName($updatedName);
    $key->setWebSettings($settings);

    $updateMask = new FieldMask([
        'paths' => ['display_name', 'web_settings']
    ]);

    try {
        $updatedKey = $client->updateKey($key, [
            'updateMask' => $updateMask
        ]);

        printf('The key: %s is updated.' . PHP_EOL, $updatedKey->getDisplayName());
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('The key with Key ID: %s doesn\'t exist.' . PHP_EOL, $keyId);
        } else {
            print('updateKey() call failed with the following error: ');
            print($e);
        }
    }
}
// [END recaptcha_enterprise_update_site_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
