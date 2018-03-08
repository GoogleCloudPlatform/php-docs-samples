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
namespace Google\Cloud\Samples\Dlp;

# [START dlp_numerical_stats]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\RiskAnalysisJobConfig;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\DlpJob_JobState;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action_PublishToPubSub;
use Google\Cloud\Dlp\V2\PrivacyMetric_NumericalStatsConfig;
use Google\Cloud\Dlp\V2\PrivacyMetric;
use Google\Cloud\Dlp\V2\FieldId;

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
    $dlp = new DlpServiceClient();
    $pubsub = new PubSubClient();

    // Construct risk analysis config
    $columnField = new FieldId();
    $columnField->setName($columnName);

    $statsConfig = new PrivacyMetric_NumericalStatsConfig();
    $statsConfig->setField($columnField);

    $privacyMetric = new PrivacyMetric();
    $privacyMetric->setNumericalStatsConfig($statsConfig);

    // Construct items to be analyzed
    $bigqueryTable = new BigQueryTable();
    $bigqueryTable->setProjectId($dataProjectId);
    $bigqueryTable->setDatasetId($datasetId);
    $bigqueryTable->setTableId($tableId);

    // Construct the action to run when job completes
    $fullTopicId = 'projects/' . $callingProjectId . '/topics/' . $topicId;
    $pubSubAction = new Action_PublishToPubSub();
    $pubSubAction->setTopic($fullTopicId);

    $action = new Action();
    $action->setPubSub($pubSubAction);

    // Construct risk analysis job config to run
    $riskJob = new RiskAnalysisJobConfig();
    $riskJob->setPrivacyMetric($privacyMetric);
    $riskJob->setSourceTable($bigqueryTable);
    $riskJob->setActions([$action]);

    // Listen for job notifications via an existing topic/subscription.
    $topic = $pubsub->topic($topicId);
    $subscription = $topic->subscription($subscriptionId);

    // Submit request
    $parent = $dlp->projectName($callingProjectId);
    $job = $dlp->createDlpJob($parent, [
        'riskJob' => $riskJob
    ]);

    // Poll via Pub/Sub until job finishes
    $polling = true;
    while ($polling) {
        foreach ($subscription->pull() as $message) {
            $subscription->acknowledge($message);
            if (isset($message->attributes()['DlpJobName']) &&
                $message->attributes()['DlpJobName'] === $job->getName()) {
                $polling = false;
            }
        }
    }

    // Get the updated job
    $job = $dlp->getDlpJob($job->getName());

    // Helper function to convert Protobuf values to strings
    $value_to_string = function ($value) {
        return $value->getIntegerValue() ?:
            $value->getFloatValue() ?:
            $value->getStringValue() ?:
            $value->getBooleanValue() ?:
            $value->getTimestampValue() ?:
            $value->getTimeValue() ?:
            $value->getDateValue() ?:
            $value->getDayOfWeekValue();
    };

    // Print finding counts
    print_r('Job ' . $job->getName() . ' status: ' . $job->getState() . PHP_EOL);
    switch ($job->getState()) {
        case DlpJob_JobState::DONE:
            $results = $job->getRiskDetails()->getNumericalStatsResult();
            print_r(
                'Value range: [' .
                $value_to_string($results->getMinValue()) .
                ', ' .
                $value_to_string($results->getMaxValue()) .
                ']' . PHP_EOL
            );

            // Only print unique values
            $lastValue = null;
            foreach ($results->getQuantileValues() as $percent => $quantileValue) {
                $value = $value_to_string($quantileValue);
                if ($value != $lastValue) {
                    print_r('Value at ' . $percent . ' quantile: ' . $value . PHP_EOL);
                    $lastValue = $value;
                }
            }
            
            break;
        case DlpJob_JobState::ERROR:
            $errors = $job->getErrors();
            foreach ($errors as $error) {
                var_dump($error->getDetails());
            }
            print_r('Job ' . $job->getName() . ' had errors:' . PHP_EOL);
            break;
        default:
            print_r('Unknown job state. Most likely, the job is either running or has not yet started.');
    }
}
# [END dlp_numerical_stats]
