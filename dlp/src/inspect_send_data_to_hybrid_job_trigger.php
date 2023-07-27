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

# [START dlp_inspect_send_data_to_hybrid_job_trigger]

use Google\Cloud\Dlp\V2\Container;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\DlpJob\JobState;
use Google\Cloud\Dlp\V2\HybridContentItem;
use Google\Cloud\Dlp\V2\HybridFindingDetails;

/**
 * Inspect data hybrid job trigger.
 * Send data to the hybrid job or hybrid job trigger.
 *
 * @param string $callingProjectId  The Google Cloud project id to use as a parent resource.
 * @param string $string            The string to inspect (will be treated as text).
 */

function inspect_send_data_to_hybrid_job_trigger(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $jobTriggerId,
    string $string
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $content = (new ContentItem())
        ->setValue($string);

    $container = (new Container())
        ->setFullPath('10.0.0.2:logs1:app1')
        ->setRelativePath('app1')
        ->setRootPath('10.0.0.2:logs1')
        ->setType('logging_sys')
        ->setVersion('1.2');

    $findingDetails = (new HybridFindingDetails())
        ->setContainerDetails($container)
        ->setLabels([
            'env' => 'prod',
            'appointment-bookings-comments' => ''
        ]);

    $hybridItem = (new HybridContentItem())
        ->setItem($content)
        ->setFindingDetails($findingDetails);

    $parent = "projects/$callingProjectId/locations/global";
    $name = "projects/$callingProjectId/locations/global/jobTriggers/" . $jobTriggerId;

    $triggerJob = null;
    try {
        $triggerJob = $dlp->activateJobTrigger($name);
    } catch (\Exception $e) {
        $result = $dlp->listDlpJobs($parent, ['filter' => 'trigger_name=' . $name]);
        foreach ($result as $job) {
            $triggerJob = $job;
        }
    }

    $dlp->hybridInspectJobTrigger($name, [
        'hybridItem' => $hybridItem,
    ]);

    $numOfAttempts = 10;
    do {
        printf('Waiting for job to complete' . PHP_EOL);
        sleep(10);
        $job = $dlp->getDlpJob($triggerJob->getName());
        if ($job->getState() != JobState::RUNNING) {
            break;
        }
        $numOfAttempts--;
    } while ($numOfAttempts > 0);

    // Print finding counts.
    printf('Job %s status: %s' . PHP_EOL, $job->getName(), JobState::name($job->getState()));
    switch ($job->getState()) {
        case JobState::DONE:
            $infoTypeStats = $job->getInspectDetails()->getResult()->getInfoTypeStats();
            if (count($infoTypeStats) === 0) {
                printf('No findings.' . PHP_EOL);
            } else {
                foreach ($infoTypeStats as $infoTypeStat) {
                    printf(
                        '  Found %s instance(s) of infoType %s' . PHP_EOL,
                        $infoTypeStat->getCount(),
                        $infoTypeStat->getInfoType()->getName()
                    );
                }
            }
            break;
        case JobState::FAILED:
            printf('Job %s had errors:' . PHP_EOL, $job->getName());
            $errors = $job->getErrors();
            foreach ($errors as $error) {
                var_dump($error->getDetails());
            }
            break;
        case JobState::PENDING:
            printf('Job has not completed. Consider a longer timeout or an asynchronous execution model' . PHP_EOL);
            break;
        default:
            printf('Unexpected job state. Most likely, the job is either running or has not yet started.');
    }
}
# [END dlp_inspect_send_data_to_hybrid_job_trigger]

// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
