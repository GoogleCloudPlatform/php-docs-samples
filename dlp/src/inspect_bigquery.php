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

# [START dlp_inspect_bigquery]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\BigQueryOptions;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\StorageConfig;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\DlpJob_JobState;
use Google\Cloud\Dlp\V2\InspectConfig_FindingLimits;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action_PublishToPubSub;
use Google\Cloud\Dlp\V2\InspectJobConfig;

/**
 * Inspect a BigQuery table using the Data Loss Prevention (DLP) API.
 *
 * @param string $callingProjectId The project ID to run the API call under
 * @param string $dataProjectId The project ID containing the target Datastore
 * @param string $topicId The name of the Pub/Sub topic to notify once the job completes
 * @param string $subscriptionId The name of the Pub/Sub subscription to use when listening for job
 * @param string $datasetId The ID of the dataset to inspect
 * @param string $tableId The ID of the table to inspect
 * @param int $maxFindings The maximum number of findings to report per request (0 = server maximum)
 */
function inspect_bigquery(
    $callingProjectId,
    $dataProjectId,
    $topicId,
    $subscriptionId,
    $datasetId,
    $tableId,
    $maxFindings = 0)
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();
    $pubsub = new PubSubClient([
        'projectId' => $callingProjectId // TODO is this necessary?
    ]);

    // The infoTypes of information to match
    $personNameInfoType = new InfoType();
    $personNameInfoType->setName('PERSON_NAME');
    $creditCardNumberInfoType = new InfoType();
    $creditCardNumberInfoType->setName('CREDIT_CARD_NUMBER');
    $infoTypes = [$personNameInfoType, $creditCardNumberInfoType];

    // The minimum likelihood required before returning a match
    $minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED;

    // Specify finding limits
    $limits = new InspectConfig_FindingLimits();
    $limits->setMaxFindingsPerRequest($maxFindings);

    // Construct items to be inspected
    $bigqueryTable = new BigQueryTable();
    $bigqueryTable->setProjectId($dataProjectId);
    $bigqueryTable->setDatasetId($datasetId);
    $bigqueryTable->setTableId($tableId);

    $bigQueryOptions = new BigQueryOptions();
    $bigQueryOptions->setTableReference($bigqueryTable);

    $storageConfig = new StorageConfig();
    $storageConfig->setBigQueryOptions($bigQueryOptions);

    // Construct the inspect config object
    $inspectConfig = new InspectConfig();
    $inspectConfig->setMinLikelihood($minLikelihood);
    $inspectConfig->setLimits($limits);
    $inspectConfig->setInfoTypes($infoTypes);

    // Construct the action to run when job completes
    $fullTopicId = 'projects/' . $callingProjectId . '/topics/' . $topicId;
    $pubSubAction = new Action_PublishToPubSub();
    $pubSubAction->setTopic($fullTopicId);

    $action = new Action();
    $action->setPubSub($pubSubAction);

    // Construct inspect job config to run
    $inspectJob = new InspectJobConfig();
    $inspectJob->setInspectConfig($inspectConfig);
    $inspectJob->setStorageConfig($storageConfig);
    $inspectJob->setActions([$action]);

    // Listen for job notifications via an existing topic/subscription.
    $topic = $pubsub->topic($topicId);
    $subscription = $topic->subscription($subscriptionId);

    // Submit request
    $parent = $dlp->projectName($callingProjectId);
    $job = $dlp->createDlpJob($parent, [
        'inspectJob' => $inspectJob
    ]);

    // Poll via Pub/Sub until job finishes
    // TODO is there a better way to do this?
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

    // Print finding counts
    print_r('Job ' . $job->getName() . ' status: ' . $job->getState() . PHP_EOL);
    switch ($job->getState()) {
        case DlpJob_JobState::DONE:
            $infoTypeStats = $job->getInspectDetails()->getResult()->getInfoTypeStats();
            if (count($infoTypeStats) === 0) {
                print_r('No findings.' . PHP_EOL);
            } else {
                foreach ($infoTypeStats as $infoTypeStat) {
                    print_r('  Found ' . $infoTypeStat->getCount() . ' instance(s) of infoType ' .  $infoTypeStat->getInfoType()->getName() . PHP_EOL);
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
# [END dlp_inspect_bigquery]
