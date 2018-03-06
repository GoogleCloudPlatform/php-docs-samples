<?php

/**
 * Copyright 2016 Google Inc.
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
 * @param string $callingProject The GCP Project ID to run the API call under
 */
function list_inspect_templates($callingProject)
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = $dlp->projectName($callingProject);

    // Run request
    $response = $dlp->listInspectTemplates($parent);

    // Print results
    $templates = $response->iterateAllElements();

    foreach ($templates as $template) {
        print_r('Template ' . $template->getName() . PHP_EOL);
        print_r('  Created: ' . $template->getCreateTime()->getSeconds() . PHP_EOL);
        print_r('  Updated: ' . $template->getUpdateTime()->getSeconds() . PHP_EOL);
        print_r('  Display Name: ' . $template->getDisplayName() . PHP_EOL);
        print_r('  Description: ' . $template->getDescription() . PHP_EOL);

        $inspectConfig = $template->getInspectConfig();
        print_r('  Minimum likelihood: ' . $inspectConfig->getMinLikelihood() . PHP_EOL);
        print_r('  Include quotes: ' . $inspectConfig->getIncludeQuote() . PHP_EOL);

        $limits = $inspectConfig->getLimits();
        print_r('  Max findings per request:' . $limits->getMaxFindingsPerRequest() . PHP_EOL);
    }
}
// [END dlp_list_inspect_templates]
