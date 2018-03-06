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

# [START dlp_list_triggers]
use Google\Cloud\Dlp\V2\DlpServiceClient;

/**
 * List Data Loss Prevention API job triggers.
 * @param string $callingProject The GCP Project ID to run the API call under
 */
function list_triggers($callingProject)
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = $dlp->projectName($callingProject);

    // Run request
    $response = $dlp->listJobTriggers($parent);

    // Print results
    $triggers = $response->iterateAllElements();
    foreach ($triggers as $trigger) {
        print_r('Trigger ' . $trigger->getName() . PHP_EOL);
        print_r('  Created: ' . $trigger->getCreateTime()->getSeconds() . PHP_EOL);
        print_r('  Updated: ' . $trigger->getUpdateTime()->getSeconds() . PHP_EOL);
        print_r('  Display Name: ' . $trigger->getDisplayName() . PHP_EOL);
        print_r('  Description: ' . $trigger->getDescription() . PHP_EOL);
        print_r('  Status: ' . $trigger->getStatus() . PHP_EOL);
        print_r('  Error count: ' . count($trigger->getErrors()) . PHP_EOL);
    }
}
# [END dlp_list_triggers]
