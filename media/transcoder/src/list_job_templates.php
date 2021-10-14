<?php

/**
 * Copyright 2021 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/media/transcoder/README.md
 */

namespace Google\Cloud\Samples\Media\Transcoder;

# [START transcoder_list_job_templates]
use Google\Cloud\Video\Transcoder\V1\TranscoderServiceClient;

/**
 * Lists all Transcoder job templates in a location.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 * @param string $location The location of the job templates.
 */
function list_job_templates($projectId, $location)
{
    // Instantiate a client.
    $transcoderServiceClient = new TranscoderServiceClient();

    $formattedParent = $transcoderServiceClient->locationName($projectId, $location);
    $response = $transcoderServiceClient->listJobTemplates($formattedParent);

    // Print job template list.
    $jobTemplates = $response->iterateAllElements();
    print('Job templates:' . PHP_EOL);
    foreach ($jobTemplates as $jobTemplate) {
        printf('%s' . PHP_EOL, $jobTemplate->getName());
    }
}
# [END transcoder_list_job_templates]

require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
