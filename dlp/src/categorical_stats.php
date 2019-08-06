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

if (count($argv) != 8) {
    return print("Usage: php categorical_stats.php CALLING_PROJECT DATA_PROJECT TOPIC SUBSCRIPTION DATASET TABLE COLUMN\n");
}
list($_, $callingProjectId, $dataProjectId, $topicId, $subscriptionId, $datasetId, $tableId, $columnName) = $argv;

# [START dlp_categorical_stats]
/**
 * Computes risk metrics of a column of data in a Google BigQuery table.
 */
use Google\Cloud\Core\ExponentialBackoff;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\RiskAnalysisJobConfig;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\DlpJob\JobState;
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action\PublishToPubSub;
use Google\Cloud\Dlp\V2\PrivacyMetric\CategoricalStatsConfig;
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
// $columnName = 'The name of the column to compute risk metrics for, e.g. "age"';

// Instantiate a client.
$dlp = new DlpServiceClient([
    'projectId' => $callingProjectId,
]);
$pubsub = new PubSubClient([
    'projectId' => $callingProjectId,
]);
$topic = $pubsub->topic($topicId);

// Construct risk analysis config
$columnField = (new FieldId())
    ->setName($columnName);

$statsConfig = (new CategoricalStatsConfig())
    ->setField($columnField);

$privacyMetric = (new PrivacyMetric())
    ->setCategoricalStatsConfig($statsConfig);

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

// Submit request
$parent = $dlp->projectName($callingProjectId);
$job = $dlp->createDlpJob($parent, [
    'riskJob' => $riskJob
]);

// Listen for job notifications via an existing topic/subscription.
$subscription = $topic->subscription($subscriptionId);

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
        $histBuckets = $job->getRiskDetails()->getCategoricalStatsResult()->getValueFrequencyHistogramBuckets();

        foreach ($histBuckets as $bucketIndex => $histBucket) {
            // Print bucket stats
            printf('Bucket %s:' . PHP_EOL, $bucketIndex);
            printf('  Most common value occurs %s time(s)' . PHP_EOL, $histBucket->getValueFrequencyUpperBound());
            printf('  Least common value occurs %s time(s)' . PHP_EOL, $histBucket->getValueFrequencyLowerBound());
            printf('  %s unique value(s) total.', $histBucket->getBucketSize());

            // Print bucket values
            foreach ($histBucket->getBucketValues() as $percent => $quantile) {
                printf(
                    '  Value %s occurs %s time(s).' . PHP_EOL,
                    $quantile->getValue()->serializeToJsonString(),
                    $quantile->getCount()
                );
            }
        }

        break;
    case JobState::FAILED:
        $errors = $job->getErrors();
        printf('Job %s had errors:' . PHP_EOL, $job->getName());
        foreach ($errors as $error) {
            var_dump($error->getDetails());
        }
        break;
    default:
        printf('Unexpected job state.');
}
# [END dlp_categorical_stats]
