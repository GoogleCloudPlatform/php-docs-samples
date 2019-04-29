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
namespace Google\Cloud\Samples\Dlp;

# [START dlp_numerical_stats]
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

/**
 * Computes risk metrics of a column of numbers in a Google BigQuery table.
 *
 * @param string $callingProjectId The project ID to run the API call under
 * @param string $dataProjectId The project ID containing the target Datastore
 * @param string $topicId The name of the Pub/Sub topic to notify once the job completes
 * @param string $subscriptionId The name of the Pub/Sub subscription to use when listening for job
 * @param string $datasetId The ID of the dataset to inspect
 * @param string $tableId The ID of the table to inspect
 * @param string $columnName The name of the (number-type) column to compute risk metrics for, e.g. 'age'
 */
function numerical_stats(
    $callingProjectId,
    $dataProjectId,
    $topicId,
    $subscriptionId,
    $datasetId,
    $tableId,
    $columnName
) {
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

    // Poll via Pub/Sub until job finishes
    while (true) {
        foreach ($subscription->pull() as $message) {
            if (isset($message->attributes()['DlpJobName']) &&
                $message->attributes()['DlpJobName'] === $job->getName()) {
                $subscription->acknowledge($message);
                break 2;
            }
        }
    }

    // Get the updated job
    $job = $dlp->getDlpJob($job->getName());

    // Sleep to avoid race condition with the job's status.
    while ($job->getState() == JobState::RUNNING) {
        usleep(1000000);
        $job = $dlp->getDlpJob($job->getName());
    }

    // Helper function to convert Protobuf values to strings
    $value_to_string = function ($value) {
        $json = json_decode($value->serializeToJsonString(), true);
        return array_shift($json);
    };

    // Print finding counts
    printf('Job %s status: %s' . PHP_EOL, $job->getName(), $job->getState());
    switch ($job->getState()) {
        case JobState::DONE:
            $results = $job->getRiskDetails()->getNumericalStatsResult();
            printf(
                'Value range: [%s, %s]' . PHP_EOL,
                $value_to_string($results->getMinValue()),
                $value_to_string($results->getMaxValue())
            );

            // Only print unique values
            $lastValue = null;
            foreach ($results->getQuantileValues() as $percent => $quantileValue) {
                $value = $value_to_string($quantileValue);
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
}
# [END dlp_numerical_stats]
