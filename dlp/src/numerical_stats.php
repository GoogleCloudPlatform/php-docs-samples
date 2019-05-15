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
    return print("Usage: php numerical_stats.php CALLING_PROJECT DATA_PROJECT TOPIC SUBSCRIPTION DATASET TABLE COLUMN\n");
}
list($_, $callingProjectId, $dataProjectId, $topicId, $subscriptionId, $datasetId, $tableId, $columnName) = $argv;

# [START dlp_numerical_stats]
/**
 * Computes risk metrics of a column of numbers in a Google BigQuery table.
 */
use Google\Cloud\Core\ExponentialBackoff;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\RiskAnalysisJobConfig;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\DlpJob\JobState;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action\PublishToPubSub;
use Google\Cloud\Dlp\V2\PrivacyMetric\NumericalStatsConfig;
use Google\Cloud\Dlp\V2\PrivacyMetric;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\PubSub\V1\PublisherClient;

/** Uncomment and populate these variables in your code */
// $callingProjectId = 'The project ID to run the API call under';
// $dataProjectId = 'The project ID containing the target Datastore';
// $topicId = 'The name of the Pub/Sub topic to notify once the job completes';
// $subscriptionId = 'The name of the Pub/Sub subscription to use when listening for job';
// $datasetId = 'The ID of the BigQuery dataset to inspect';
// $tableId = 'The ID of the BigQuery table to inspect';
// $columnName = 'The name of the column to compute risk metrics for, e.g. "age"';

// Instantiate a client.
$dlp = new DlpServiceClient([
    'projectId' => $callingProjectId
]);
$pubsub = new PubSubClient([
    'projectId' => $callingProjectId
]);

// Construct risk analysis config
$columnField = (new FieldId())
    ->setName($columnName);

$statsConfig = (new NumericalStatsConfig())
    ->setField($columnField);

$privacyMetric = (new PrivacyMetric())
    ->setNumericalStatsConfig($statsConfig);

// Construct items to be analyzed
$bigqueryTable = (new BigQueryTable())
    ->setProjectId($dataProjectId)
    ->setDatasetId($datasetId)
    ->setTableId($tableId);

// Construct the action to run when job completes
$fullTopicId = PublisherClient::topicName($callingProjectId, $topicId);
$pubSubAction = (new PublishToPubSub())
    ->setTopic($fullTopicId);

$action = (new Action())
    ->setPubSub($pubSubAction);

// Construct risk analysis job config to run
$riskJob = (new RiskAnalysisJobConfig())
    ->setPrivacyMetric($privacyMetric)
    ->setSourceTable($bigqueryTable)
    ->setActions([$action]);

// Listen for job notifications via an existing topic/subscription.
$topic = $pubsub->topic($topicId);
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

// Helper function to convert Protobuf values to strings
$valueToString = function ($value) {
    $json = json_decode($value->serializeToJsonString(), true);
    return array_shift($json);
};

// Print finding counts
printf('Job %s status: %s' . PHP_EOL, $job->getName(), JobState::name($job->getState()));
switch ($job->getState()) {
    case JobState::DONE:
        $results = $job->getRiskDetails()->getNumericalStatsResult();
        printf(
            'Value range: [%s, %s]' . PHP_EOL,
            $valueToString($results->getMinValue()),
            $valueToString($results->getMaxValue())
        );

        // Only print unique values
        $lastValue = null;
        foreach ($results->getQuantileValues() as $percent => $quantileValue) {
            $value = $valueToString($quantileValue);
            if ($value != $lastValue) {
                printf('Value at %s quantile: %s' . PHP_EOL, $percent, $value);
                $lastValue = $value;
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
# [END dlp_numerical_stats]
