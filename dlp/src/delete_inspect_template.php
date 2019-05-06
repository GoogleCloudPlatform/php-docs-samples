<?php

/**
 * Copyright 2018 Google Inc.
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
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/dlp/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 3) {
    return print("Usage: php delete_inspect_template.php CALLING_PROJECT TEMPLATE\n");
}
list($_, $callingProjectId, $templateId) = $argv;

// [START dlp_delete_inspect_template]
/**
 * Delete a DLP inspection configuration template.
 */
use Google\Cloud\Dlp\V2\DlpServiceClient;

/** Uncomment and populate these variables in your code */
// $callingProjectId = 'The project ID to run the API call under';
// $templateId = 'The name of the template to delete';

// Instantiate a client.
$dlp = new DlpServiceClient();

// Run template deletion request
$templateName = $dlp->projectInspectTemplateName($callingProjectId, $templateId);
$dlp->deleteInspectTemplate($templateName);

// Print results
printf('Successfully deleted template %s' . PHP_EOL, $templateName);
// [END dlp_delete_inspect_template]
