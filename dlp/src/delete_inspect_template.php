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

namespace Google\Cloud\Samples\Dlp;

// [START dlp_delete_inspect_template]
use Google\Cloud\Dlp\V2\DlpServiceClient;

/**
 * Delete a DLP inspection configuration template.
 *
 * @param string $callingProjectId  The project ID to run the API call under
 * @param string $templateId        The name of the template to delete
 */
function delete_inspect_template(
    string $callingProjectId,
    string $templateId
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Run template deletion request
    $templateName = "projects/$callingProjectId/locations/global/inspectTemplates/$templateId";
    $dlp->deleteInspectTemplate($templateName);

    // Print results
    printf('Successfully deleted template %s' . PHP_EOL, $templateName);
}
// [END dlp_delete_inspect_template]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
