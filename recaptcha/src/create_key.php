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

// [START recaptcha_enterprise_create_site_key]
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Key;
use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings;
use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings\IntegrationType;
use Google\ApiCore\ApiException;

/**
 * Create a site key for reCAPTCHA
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $keyName The name of the key you wish to create
 */
function create_key(string $projectId, string $keyName): void
{
    $client = new RecaptchaEnterpriseServiceClient();
    $formattedProject = $client->projectName($projectId);

    // Create the settings for the key.
    // In order to create other keys we'll use AndroidKeySettings or IOSKeySettings
    $settings = new WebKeySettings();

    // Allow the key to work for all domains(Not recommended)
    $settings->setAllowAllDomains(true);
    // ...or explicitly set the allowed domains for the key as an array of strings
    // $settings->setAllowedDomains(['']);

    // Specify the type of the key
    // - score based key -> IntegrationType::SCORE
    // - checkbox based key -> IntegrationType::CHECKBOX
    // Read https://cloud.google.com/recaptcha-enterprise/docs/choose-key-type
    $settings->setIntegrationType(IntegrationType::CHECKBOX);

    $key = new Key();
    $key->setDisplayName($keyName);
    $key->setWebSettings($settings);

    try {
        $createdKey = $client->createKey($formattedProject, $key);
        printf('The key: %s is created.' . PHP_EOL, $createdKey->getName());
    } catch (ApiException $e) {
        print('createKey() call failed with the following error: ');
        print($e);
    }
}
// [END recaptcha_enterprise_create_site_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
