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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 10) {
    return print("Usage: php k_map.php CALLING_PROJECT DATA_PROJECT TOPIC SUBSCRIPTION DATASET TABLE REGION_CODE QUASI_ID_NAMES INFO_TYPES\n");
}
list($_, $callingProjectId, $dataProjectId, $topicId, $subscriptionId, $datasetId, $tableId, $regionCode, $quasiIdNames, $infoTypes) = $argv;
// Convert comma-separated lists to arrays
$quasiIdNames = explode(',', $quasiIdNames);
$infoTypes = explode(',', $infoTypes);

# [START dlp_k_map]
/**
 * Computes the k-map risk estimation of a column set in a Google BigQuery table.
 */
use Google\Cloud\Core\ExponentialBackoff;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\RiskAnalysisJobConfig;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\DlpJob\JobState;
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action\PublishToPubSub;
use Google\Cloud\Dlp\V2\PrivacyMetric\KMapEstimationConfig;
use Google\Cloud\Dlp\V2\PrivacyMetric\KMapEstimationConfig\TaggedField;
use Google\Cloud\Dlp\V2\PrivacyMetric;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\PubSub\PubSubClient;

/** Uncomment and populate these variables in your code */
// $callingProjectId = 'The project ID to run the API call under';
// $dataProjectId = 'The project ID containing the target Datastore';
// $topicId = 'The name of the Pub/Sub topic to notify once the job completes';
// $subscriptionId = 'The name of the Pub/Sub subscription to use when listening for job';
// $datasetId = 'The ID of the dataset to inspect';
// $tableId = 'The ID of the table to inspect';
// $regionCode = 'The ISO 3166-1 region code that the data is representative of';
// $quasiIdNames = ['array columns that form a composite key (quasi-identifiers)'];
// $infoTypes = ['array of infoTypes corresponding to the chosen quasi-identifiers'];

// Instantiate a client.
$dlp = new DlpServiceClient([
    'projectId' => $callingProjectId,
]);
$pubsub = new PubSubClient([
    'projectId' => $callingProjectId,
]);
$topic = $pubsub->topic($topicId);

// Verify input
if (count($infoTypes) != count($quasiIdNames)) {
    throw new Exception('Number of infoTypes and number of quasi-identifiers must be equal!');
}

// Map infoTypes to quasi-ids
$quasiIdObjects = array_map(function ($quasiId, $infoType) {
    $quasiIdField = (new FieldId())
        ->setName($quasiId);

    $quasiIdType = (new InfoType())
        ->setName($infoType);

    $quasiIdObject = (new TaggedField())
        ->setInfoType($quasiIdType)
        ->setField($quasiIdField);

    return $quasiIdObject;
}, $quasiIdNames, $infoTypes);

// Construct analysis config
$statsConfig = (new KMapEstimationConfig())
    ->setQuasiIds($quasiIdObjects)
    ->setRegionCode($regionCode);

$privacyMetric = (new PrivacyMetric())
    ->setKMapEstimationConfig($statsConfig);

// Construct items to be analyzed
$bigqueryTable = (new BigQueryTable())
    ->setProjectId($dataProjectId)
    ->setDatasetId($datasetId)
    ->setTableId($tableId);

// Construct the action to run when job completes
$pubSubAction = (new PublishToPubSub())
    ->setTopic($topic->name());

$action = (new Action())
    ->setPubSub($pubSubAction);

// Construct risk analysis job config to run
$riskJob = (new RiskAnalysisJobConfig())
    ->setPrivacyMetric($privacyMetric)
    ->setSourceTable($bigqueryTable)
    ->setActions([$action]);

// Listen for job notifications via an existing topic/subscription.
$subscription = $topic->subscription($subscriptionId);

// Submit request
$parent = $dlp->projectName($callingProjectId);
$job = $dlp->createDlpJob($parent, [
    'riskJob' => $riskJob
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
        $histBuckets = $job->getRiskDetails()->getKMapEstimationResult()->getKMapEstimationHistogram();

        foreach ($histBuckets as $bucketIndex => $histBucket) {
            // Print bucket stats
            printf('Bucket %s:' . PHP_EOL, $bucketIndex);
            printf(
                '  Anonymity range: [%s, %s]' . PHP_EOL,
                $histBucket->getMinAnonymity(),
                $histBucket->getMaxAnonymity()
            );
            printf('  Size: %s' . PHP_EOL, $histBucket->getBucketSize());

            // Print bucket values
            foreach ($histBucket->getBucketValues() as $percent => $valueBucket) {
                printf(
                    '  Estimated k-map anonymity: %s' . PHP_EOL,
                    $valueBucket->getEstimatedAnonymity()
                );

                // Pretty-print quasi-ID values
                print('  Values: ' . PHP_EOL);
                foreach ($valueBucket->getQuasiIdsValues() as $index => $value) {
                    print('    ' . $value->serializeToJsonString() . PHP_EOL);
                }
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
        print('Unexpected job state. Most likely, the job is either running or has not yet started.');
}
# [END dlp_k_map]
