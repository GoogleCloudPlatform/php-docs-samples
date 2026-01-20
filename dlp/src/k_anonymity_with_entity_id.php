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

# [START dlp_k_anonymity_with_entity_id]
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action\SaveFindings;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\CreateDlpJobRequest;
use Google\Cloud\Dlp\V2\DlpJob\JobState;
use Google\Cloud\Dlp\V2\EntityId;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\Dlp\V2\GetDlpJobRequest;
use Google\Cloud\Dlp\V2\OutputStorageConfig;
use Google\Cloud\Dlp\V2\PrivacyMetric;
use Google\Cloud\Dlp\V2\PrivacyMetric\KAnonymityConfig;
use Google\Cloud\Dlp\V2\RiskAnalysisJobConfig;

/**
 * Computes the k-anonymity of a column set in a Google BigQuery table with entity id.
 *
 * @param string    $callingProjectId  The project ID to run the API call under.
 * @param string    $datasetId         The ID of the dataset to inspect.
 * @param string    $tableId           The ID of the table to inspect.
 * @param string[]  $quasiIdNames      Array columns that form a composite key (quasi-identifiers).
 */

function k_anonymity_with_entity_id(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $datasetId,
    string $tableId,
    array  $quasiIdNames
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Specify the BigQuery table to analyze.
    $bigqueryTable = (new BigQueryTable())
        ->setProjectId($callingProjectId)
        ->setDatasetId($datasetId)
        ->setTableId($tableId);

    // Create a list of FieldId objects based on the provided list of column names.
    $quasiIds = array_map(
        function ($id) {
            return (new FieldId())
                ->setName($id);
        },
        $quasiIdNames
    );

    // Specify the unique identifier in the source table for the k-anonymity analysis.
    $statsConfig = (new KAnonymityConfig())
        ->setEntityId((new EntityId())
            ->setField((new FieldId())
                ->setName('Name')))
        ->setQuasiIds($quasiIds);

    // Configure the privacy metric to compute for re-identification risk analysis.
    $privacyMetric = (new PrivacyMetric())
        ->setKAnonymityConfig($statsConfig);

    // Specify the bigquery table to store the findings.
    // The "test_results" table in the given BigQuery dataset will be created if it doesn't
    // already exist.
    $outBigqueryTable = (new BigQueryTable())
        ->setProjectId($callingProjectId)
        ->setDatasetId($datasetId)
        ->setTableId('test_results');

    $outputStorageConfig = (new OutputStorageConfig())
        ->setTable($outBigqueryTable);

    $findings = (new SaveFindings())
        ->setOutputConfig($outputStorageConfig);

    $action = (new Action())
        ->setSaveFindings($findings);

    // Construct risk analysis job config to run.
    $riskJob = (new RiskAnalysisJobConfig())
        ->setPrivacyMetric($privacyMetric)
        ->setSourceTable($bigqueryTable)
        ->setActions([$action]);

    // Submit request.
    $parent = "projects/$callingProjectId/locations/global";
    $createDlpJobRequest = (new CreateDlpJobRequest())
        ->setParent($parent)
        ->setRiskJob($riskJob);
    $job = $dlp->createDlpJob($createDlpJobRequest);

    $numOfAttempts = 10;
    do {
        printf('Waiting for job to complete' . PHP_EOL);
        sleep(10);
        $getDlpJobRequest = (new GetDlpJobRequest())
            ->setName($job->getName());
        $job = $dlp->getDlpJob($getDlpJobRequest);
        if ($job->getState() == JobState::DONE) {
            break;
        }
        $numOfAttempts--;
    } while ($numOfAttempts > 0);

    // Print finding counts
    printf('Job %s status: %s' . PHP_EOL, $job->getName(), JobState::name($job->getState()));
    switch ($job->getState()) {
        case JobState::DONE:
            $histBuckets = $job->getRiskDetails()->getKAnonymityResult()->getEquivalenceClassHistogramBuckets();

            foreach ($histBuckets as $bucketIndex => $histBucket) {
                // Print bucket stats.
                printf('Bucket %s:' . PHP_EOL, $bucketIndex);
                printf(
                    '  Bucket size range: [%s, %s]' . PHP_EOL,
                    $histBucket->getEquivalenceClassSizeLowerBound(),
                    $histBucket->getEquivalenceClassSizeUpperBound()
                );

                // Print bucket values.
                foreach ($histBucket->getBucketValues() as $percent => $valueBucket) {
                    // Pretty-print quasi-ID values.
                    printf('  Quasi-ID values:' . PHP_EOL);
                    foreach ($valueBucket->getQuasiIdsValues() as $index => $value) {
                        print('    ' . $value->serializeToJsonString() . PHP_EOL);
                    }
                    printf(
                        '  Class size: %s' . PHP_EOL,
                        $valueBucket->getEquivalenceClassSize()
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
# [END dlp_k_anonymity_with_entity_id]

// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
