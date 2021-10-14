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

# [START transcoder_create_job_from_template]
use Google\Cloud\Video\Transcoder\V1\TranscoderServiceClient;
use Google\Cloud\Video\Transcoder\V1\Job;

/**
 * Creates a job based on a job template.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 * @param string $location The location of the job.
 * @param string $inputUri Uri of the video in the Cloud Storage bucket.
 * @param string $outputUri Uri of the video output folder in the Cloud Storage bucket.
 * @param string $templateId The job template ID.
 */
function create_job_from_template($projectId, $location, $inputUri, $outputUri, $templateId)
{
    // Instantiate a client.
    $transcoderServiceClient = new TranscoderServiceClient();

    $formattedParent = $transcoderServiceClient->locationName($projectId, $location);
    $job = new Job();
    $job->setInputUri($inputUri);
    $job->setOutputUri($outputUri);
    $job->setTemplateId($templateId);

    $response = $transcoderServiceClient->createJob($formattedParent, $job);

    // Print job name.
    printf('Job: %s' . PHP_EOL, $response->getName());
}
# [END transcoder_create_job_from_template]

require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
