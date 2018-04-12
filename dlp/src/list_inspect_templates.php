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
namespace Google\Cloud\Samples\Dlp;

// [START dlp_list_inspect_templates]
use Google\Cloud\Dlp\V2\DlpServiceClient;

/**
 * List DLP inspection configuration templates.
 * @param string $callingProjectId The GCP Project ID to run the API call under
 */
function list_inspect_templates($callingProjectId)
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = $dlp->projectName($callingProjectId);

    // Run request
    $response = $dlp->listInspectTemplates($parent);

    // Print results
    $templates = $response->iterateAllElements();

    foreach ($templates as $template) {
        printf('Template %s' . PHP_EOL, $template->getName());
        printf('  Created: %s' . PHP_EOL, $template->getCreateTime()->getSeconds());
        printf('  Updated: %s' . PHP_EOL, $template->getUpdateTime()->getSeconds());
        printf('  Display Name: %s' . PHP_EOL, $template->getDisplayName());
        printf('  Description: %s' . PHP_EOL, $template->getDescription());

        $inspectConfig = $template->getInspectConfig();
        printf('  Minimum likelihood: %s' . PHP_EOL, $inspectConfig->getMinLikelihood());
        printf('  Include quotes: %s' . PHP_EOL, $inspectConfig->getIncludeQuote());

        $limits = $inspectConfig->getLimits();
        printf('  Max findings per request: %s' . PHP_EOL, $limits->getMaxFindingsPerRequest());
    }
}
// [END dlp_list_inspect_templates]
