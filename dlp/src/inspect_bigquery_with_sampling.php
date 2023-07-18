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

# [START dlp_inspect_bigquery_with_sampling]

use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\BigQueryOptions;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\StorageConfig;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\DlpJob\JobState;
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action\PublishToPubSub;
use Google\Cloud\Dlp\V2\BigQueryOptions\SampleMethod;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\Dlp\V2\InspectJobConfig;
use Google\Cloud\PubSub\PubSubClient;

/**
 * Inspect BigQuery for sensitive data with sampling.
 * The following examples demonstrate using the Cloud Data Loss Prevention API to scan a 1000-row subset of a BigQuery table. The scan starts from a random row.
 *
 * @param string $callingProjectId  The project ID to run the API call under.
 * @param string $topicId           The name of the Pub/Sub topic to notify once the job completes.
 * @param string $subscriptionId    The name of the Pub/Sub subscription to use when listening for job.
 */
function inspect_bigquery_with_sampling(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $topicId = 'dlp-pubsub-topic',
    string $subscriptionId = 'dlp_subcription'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();
    $pubsub = new PubSubClient();
    $topic = $pubsub->topic($topicId);

    // Specify the BigQuery table to be inspected.
    $bigqueryTable = (new BigQueryTable())
        ->setProjectId('bigquery-public-data')
        ->setDatasetId('usa_names')
        ->setTableId('usa_1910_current');

    $bigQueryOptions = (new BigQueryOptions())
        ->setTableReference($bigqueryTable)
        ->setRowsLimit(1000)
        ->setSampleMethod(SampleMethod::RANDOM_START)
        ->setIdentifyingFields([
            (new FieldId())
                ->setName('name')
        ]);

    $storageConfig = (new StorageConfig())
        ->setBigQueryOptions($bigQueryOptions);

    // Specify the type of info the inspection will look for.
    // See https://cloud.google.com/dlp/docs/infotypes-reference for complete list of info types
    $personNameInfoType = (new InfoType())
        ->setName('PERSON_NAME');
    $infoTypes = [$personNameInfoType];

    // Specify how the content should be inspected.
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes($infoTypes)
        ->setIncludeQuote(true);

    // Specify the action that is triggered when the job completes.
    $pubSubAction = (new PublishToPubSub())
        ->setTopic($topic->name());

    $action = (new Action())
        ->setPubSub($pubSubAction);

    // Configure the long running job we want the service to perform.
    $inspectJob = (new InspectJobConfig())
        ->setInspectConfig($inspectConfig)
        ->setStorageConfig($storageConfig)
        ->setActions([$action]);

    // Listen for job notifications via an existing topic/subscription.
    $subscription = $topic->subscription($subscriptionId);

    // Submit request
    $parent = "projects/$callingProjectId/locations/global";
    $job = $dlp->createDlpJob($parent, [
        'inspectJob' => $inspectJob
    ]);

    // Poll Pub/Sub using exponential backoff until job finishes
    // Consider using an asynchronous execution model such as Cloud Functions
    $attempt = 1;
    $startTime = time();
    do {
        foreach ($subscription->pull() as $message) {
            if (
                isset($message->attributes()['DlpJobName']) &&
                $message->attributes()['DlpJobName'] === $job->getName()
            ) {
                $subscription->acknowledge($message);
                // Get the updated job. Loop to avoid race condition with DLP API.
                do {
                    $job = $dlp->getDlpJob($job->getName());
                } while ($job->getState() == JobState::RUNNING);
                break 2; // break from parent do while
            }
        }
        printf('Waiting for job to complete' . PHP_EOL);
        // Exponential backoff with max delay of 60 seconds
        sleep(min(60, pow(2, ++$attempt)));
    } while (time() - $startTime < 600); // 10 minute timeout

    // Print finding counts
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
# [END dlp_inspect_bigquery_with_sampling]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
