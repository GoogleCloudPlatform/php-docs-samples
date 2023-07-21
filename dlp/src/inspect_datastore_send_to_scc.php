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

# [START dlp_inspect_datastore_send_to_scc]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectConfig\FindingLimits;
use Google\Cloud\Dlp\V2\StorageConfig;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action\PublishSummaryToCscc;
use Google\Cloud\Dlp\V2\DatastoreOptions;
use Google\Cloud\Dlp\V2\InspectJobConfig;
use Google\Cloud\Dlp\V2\KindExpression;
use Google\Cloud\Dlp\V2\PartitionId;
use Google\Cloud\Dlp\V2\DlpJob\JobState;

/**
 * (DATASTORE) Send Cloud DLP scan results to Security Command Center.
 * Using Cloud Data Loss Prevention to scan specific Google Cloud resources and send data to Security Command Center.
 *
 * @param string $callingProjectId  The project ID to run the API call under.
 * @param string $datastorename     Datastore kind name to be inspected.
 * @param string $namespaceId       Namespace Id to be inspected.
 */
function inspect_datastore_send_to_scc(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $datastorename = 'demo',
    string $namespaceId = 'demo'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Construct the items to be inspected.
    $datastoreOptions = (new DatastoreOptions())
        ->setKind((new KindExpression())
            ->setName($datastorename))
        ->setPartitionId((new PartitionId())
            ->setNamespaceId($namespaceId)
            ->setProjectId($callingProjectId));

    $storageConfig = (new StorageConfig())
        ->setDatastoreOptions(($datastoreOptions));

    // Specify the type of info the inspection will look for.
    $infoTypes = [
        (new InfoType())->setName('EMAIL_ADDRESS'),
        (new InfoType())->setName('PERSON_NAME'),
        (new InfoType())->setName('LOCATION'),
        (new InfoType())->setName('PHONE_NUMBER')
    ];

    // Specify how the content should be inspected.
    $inspectConfig = (new InspectConfig())
        ->setMinLikelihood(likelihood::UNLIKELY)
        ->setLimits((new FindingLimits())
            ->setMaxFindingsPerRequest(100))
        ->setInfoTypes($infoTypes)
        ->setIncludeQuote(true);

    // Specify the action that is triggered when the job completes.
    $action = (new Action())
        ->setPublishSummaryToCscc(new PublishSummaryToCscc());

    // Construct inspect job config to run.
    $inspectJobConfig = (new InspectJobConfig())
        ->setInspectConfig($inspectConfig)
        ->setStorageConfig($storageConfig)
        ->setActions([$action]);

    // Send the job creation request and process the response.
    $parent = "projects/$callingProjectId/locations/global";
    $job = $dlp->createDlpJob($parent, [
        'inspectJob' => $inspectJobConfig
    ]);

    $numOfAttempts = 10;
    do {
        printf('Waiting for job to complete' . PHP_EOL);
        sleep(10);
        $job = $dlp->getDlpJob($job->getName());
        if ($job->getState() == JobState::DONE) {
            break;
        }
        $numOfAttempts--;
    } while ($numOfAttempts > 0);

    // Print finding counts.
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
# [END dlp_inspect_datastore_send_to_scc]
// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
