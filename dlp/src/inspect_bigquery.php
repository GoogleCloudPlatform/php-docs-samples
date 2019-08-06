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

/**
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/dlp/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 7 || count($argv) > 8) {
    return print("Usage: php inspect_bigquery.php CALLING_PROJECT DATA_PROJECT TOPIC SUBSCRIPTION DATASET TABLE [MAX_FINDINGS]\n");
}
list($_, $callingProjectId, $dataProjectId, $topicId, $subscriptionId, $datasetId, $tableId) = $argv;
$maxFindings = isset($argv[7]) ? (int) $argv[7] : 0;

# [START dlp_inspect_bigquery]
/**
 * Inspect a BigQuery table , using Pub/Sub for job status notifications.
 */
use Google\Cloud\Core\ExponentialBackoff;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\BigQueryOptions;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\StorageConfig;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\DlpJob\JobState;
use Google\Cloud\Dlp\V2\InspectConfig\FindingLimits;
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action\PublishToPubSub;
use Google\Cloud\Dlp\V2\InspectJobConfig;
use Google\Cloud\PubSub\PubSubClient;

/** Uncomment and populate these variables in your code */
// $callingProjectId = 'The project ID to run the API call under';
// $dataProjectId = 'The project ID containing the target Datastore';
// $topicId = 'The name of the Pub/Sub topic to notify once the job completes';
// $subscriptionId = 'The name of the Pub/Sub subscription to use when listening for job';
// $datasetId = 'The ID of the dataset to inspect';
// $tableId = 'The ID of the table to inspect';
// $columnName = 'The name of the column to compute risk metrics for, e.g. "age"';
// $maxFindings = 0;  // (Optional) The maximum number of findings to report per request (0 = server maximum)

// Instantiate a client.
$dlp = new DlpServiceClient();
$pubsub = new PubSubClient();
$topic = $pubsub->topic($topicId);

// The infoTypes of information to match
$personNameInfoType = (new InfoType())
    ->setName('PERSON_NAME');
$creditCardNumberInfoType = (new InfoType())
    ->setName('CREDIT_CARD_NUMBER');
$infoTypes = [$personNameInfoType, $creditCardNumberInfoType];

// The minimum likelihood required before returning a match
$minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED;

// Specify finding limits
$limits = (new FindingLimits())
    ->setMaxFindingsPerRequest($maxFindings);

// Construct items to be inspected
$bigqueryTable = (new BigQueryTable())
    ->setProjectId($dataProjectId)
    ->setDatasetId($datasetId)
    ->setTableId($tableId);

$bigQueryOptions = (new BigQueryOptions())
    ->setTableReference($bigqueryTable);

$storageConfig = (new StorageConfig())
    ->setBigQueryOptions($bigQueryOptions);

// Construct the inspect config object
$inspectConfig = (new InspectConfig())
    ->setMinLikelihood($minLikelihood)
    ->setLimits($limits)
    ->setInfoTypes($infoTypes);

// Construct the action to run when job completes
$pubSubAction = (new PublishToPubSub())
    ->setTopic($topic->name());

$action = (new Action())
    ->setPubSub($pubSubAction);

// Construct inspect job config to run
$inspectJob = (new InspectJobConfig())
    ->setInspectConfig($inspectConfig)
    ->setStorageConfig($storageConfig)
    ->setActions([$action]);

// Listen for job notifications via an existing topic/subscription.
$subscription = $topic->subscription($subscriptionId);

// Submit request
$parent = $dlp->projectName($callingProjectId);
$job = $dlp->createDlpJob($parent, [
    'inspectJob' => $inspectJob
]);

// Poll Pub/Sub using exponential backoff until job finishes
$backoff = new ExponentialBackoff(30);
$backoff->execute(function () use ($subscription, $dlp, &$job) {
    printf('Waiting for job to complete' . PHP_EOL);
    foreach ($subscription->pull() as $message) {
        if (isset($message->attributes()['DlpJobName']) &&
            $message->attributes()['DlpJobName'] === $job->getName()) {
            $subscription->acknowledge($message);
            // Get the updated job. Loop to avoid race condition with DLP API.
            do {
                $job = $dlp->getDlpJob($job->getName());
            } while ($job->getState() == JobState::RUNNING);
            return true;
        }
    }
    throw new Exception('Job has not yet completed');
});

// Print finding counts
printf('Job %s status: %s' . PHP_EOL, $job->getName(), JobState::name($job->getState()));
switch ($job->getState()) {
    case JobState::DONE:
        $infoTypeStats = $job->getInspectDetails()->getResult()->getInfoTypeStats();
        if (count($infoTypeStats) === 0) {
            print('No findings.' . PHP_EOL);
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
    default:
        printf('Unexpected job state. Most likely, the job is either running or has not yet started.');
}
# [END dlp_inspect_bigquery]
