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

namespace Google\Cloud\Samples\Dlp;

# [START dlp_l_diversity]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\RiskAnalysisJobConfig;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\DlpJob\JobState;
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action\PublishToPubSub;
use Google\Cloud\Dlp\V2\PrivacyMetric\LDiversityConfig;
use Google\Cloud\Dlp\V2\PrivacyMetric;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\PubSub\PubSubClient;

/**
 * Computes the l-diversity of a column set in a Google BigQuery table.
 *
 * @param string    $callingProjectId    The project ID to run the API call under
 * @param string    $dataProjectId       The project ID containing the target Datastore
 * @param string    $topicId             The name of the Pub/Sub topic to notify once the job completes
 * @param string    $subscriptionId      The name of the Pub/Sub subscription to use when listening for job
 * @param string    $datasetId           The ID of the dataset to inspect
 * @param string    $tableId             The ID of the table to inspect
 * @param string    $sensitiveAttribute  The column to measure l-diversity relative to, e.g. "firstName"
 * @param string[]  $quasiIdNames        Array columns that form a composite key (quasi-identifiers)
 */
function l_diversity(
    string $callingProjectId,
    string $dataProjectId,
    string $topicId,
    string $subscriptionId,
    string $datasetId,
    string $tableId,
    string $sensitiveAttribute,
    array  $quasiIdNames
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient([
        'projectId' => $callingProjectId,
    ]);
    $pubsub = new PubSubClient([
        'projectId' => $callingProjectId,
    ]);
    $topic = $pubsub->topic($topicId);

    // Construct risk analysis config
    $quasiIds = array_map(
        function ($id) {
            return (new FieldId())->setName($id);
        },
        $quasiIdNames
    );

    $sensitiveField = (new FieldId())
        ->setName($sensitiveAttribute);

    $statsConfig = (new LDiversityConfig())
        ->setQuasiIds($quasiIds)
        ->setSensitiveAttribute($sensitiveField);

    $privacyMetric = (new PrivacyMetric())
        ->setLDiversityConfig($statsConfig);

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
    $parent = "projects/$callingProjectId/locations/global";
    $job = $dlp->createDlpJob($parent, [
        'riskJob' => $riskJob
    ]);

    // Poll Pub/Sub using exponential backoff until job finishes
    // Consider using an asynchronous execution model such as Cloud Functions
    $attempt = 1;
    $startTime = time();
    do {
        foreach ($subscription->pull() as $message) {
            if (isset($message->attributes()['DlpJobName']) &&
                $message->attributes()['DlpJobName'] === $job->getName()) {
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
            $histBuckets = $job->getRiskDetails()->getLDiversityResult()->getSensitiveValueFrequencyHistogramBuckets();

            foreach ($histBuckets as $bucketIndex => $histBucket) {
                // Print bucket stats
                printf('Bucket %s:' . PHP_EOL, $bucketIndex);
                printf(
                    '  Bucket size range: [%s, %s]' . PHP_EOL,
                    $histBucket->getSensitiveValueFrequencyLowerBound(),
                    $histBucket->getSensitiveValueFrequencyUpperBound()
                );

                // Print bucket values
                foreach ($histBucket->getBucketValues() as $percent => $valueBucket) {
                    printf(
                        '  Class size: %s' . PHP_EOL,
                        $valueBucket->getEquivalenceClassSize()
                    );

                    // Pretty-print quasi-ID values
                    print('  Quasi-ID values:' . PHP_EOL);
                    foreach ($valueBucket->getQuasiIdsValues() as $index => $value) {
                        print('    ' . $value->serializeToJsonString() . PHP_EOL);
                    }

                    // Pretty-print sensitive values
                    $topValues = $valueBucket->getTopSensitiveValues();
                    foreach ($topValues as $topValue) {
                        printf(
                            '  Sensitive value %s occurs %s time(s).' . PHP_EOL,
                            $topValue->getValue()->serializeToJsonString(),
                            $topValue->getCount()
                        );
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
        case JobState::PENDING:
            printf('Job has not completed. Consider a longer timeout or an asynchronous execution model' . PHP_EOL);
            break;
        default:
            printf('Unexpected job state. Most likely, the job is either running or has not yet started.');
    }
}
# [END dlp_l_diversity]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
