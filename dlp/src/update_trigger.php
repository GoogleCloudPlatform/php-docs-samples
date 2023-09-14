<?php
/**
 * Copyright 2023 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/dlp/README.md
 */

namespace Google\Cloud\Samples\Dlp;

# [START dlp_update_trigger]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectJobConfig;
use Google\Cloud\Dlp\V2\JobTrigger;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Protobuf\FieldMask;

/**
 * Update an existing job trigger.
 *
 * @param string $callingProjectId  The Google Cloud Project ID to run the API call under.
 * @param string $jobTriggerName    The job trigger name to update.
 *
 */
function update_trigger(
    string $callingProjectId,
    string $jobTriggerName
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Configure the inspectConfig.
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([
            (new InfoType())
                ->setName('US_INDIVIDUAL_TAXPAYER_IDENTIFICATION_NUMBER')
        ])
        ->setMinLikelihood(Likelihood::LIKELY);

    // Configure the Job Trigger we want the service to perform.
    $jobTrigger = (new JobTrigger())
        ->setInspectJob((new InspectJobConfig())
            ->setInspectConfig($inspectConfig));

    // Specify fields of the jobTrigger resource to be updated when the job trigger is modified.
    // Refer https://protobuf.dev/reference/protobuf/google.protobuf/#field-mask for constructing the field mask paths.
    $fieldMask = (new FieldMask())
        ->setPaths([
            'inspect_job.inspect_config.info_types',
            'inspect_job.inspect_config.min_likelihood'
        ]);

    // Send the update job trigger request and process the response.
    $name = "projects/$callingProjectId/locations/global/jobTriggers/" . $jobTriggerName;

    $response = $dlp->updateJobTrigger($name, [
        'jobTrigger' => $jobTrigger,
        'updateMask' => $fieldMask
    ]);

    // Print results.
    printf('Successfully update trigger %s' . PHP_EOL, $response->getName());
}
# [END dlp_update_trigger]
// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
