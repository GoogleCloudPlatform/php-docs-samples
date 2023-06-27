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

# [START dlp_deidentify_cloud_storage]
use Google\Cloud\Dlp\V2\CloudStorageOptions;
use Google\Cloud\Dlp\V2\CloudStorageOptions\FileSet;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\StorageConfig;
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action\Deidentify;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\FileType;
use Google\Cloud\Dlp\V2\InspectJobConfig;
use Google\Cloud\Dlp\V2\TransformationConfig;
use Google\Cloud\Dlp\V2\TransformationDetailsStorageConfig;
use Google\Cloud\Dlp\V2\Client\BaseClient\DlpServiceBaseClient;
use Google\Cloud\Dlp\V2\DlpJob\JobState;

/**
 * De-identify sensitive data stored in Cloud Storage using the API.
 * Create an inspection job that has a de-identification action.
 *
 * @param string $callingProjectId                  The project ID to run the API call under.
 * @param string inputgcsPath                       The Cloud Storage directory that you want to de-identify.
 * @param string $outgcsPath                        The Cloud Storage directory where you want to store the
 *                                                  de-identified files.
 * @param string $deidentifyTemplateName            The full resource name of the default de-identify template — for
 *                                                  unstructured and structured files — if you created one. This value
 *                                                  must be in the format
 *                                                  `projects/projectName/(locations/locationId)/deidentifyTemplates/templateName`.
 * @param string $structuredDeidentifyTemplateName  The full resource name of the de-identify template for structured
 *                                                  files if you created one. This value must be in the format
 *                                                  `projects/projectName/(locations/locationId)/deidentifyTemplates/templateName`.
 * @param string $imageRedactTemplateName           The full resource name of the image redaction template for images if
 *                                                  you created one. This value must be in the format
 *                                                  `projects/projectName/(locations/locationId)/deidentifyTemplates/templateName`.
 * @param string $datasetId                         The ID of the BigQuery dataset where you want to store
 *                                                  the transformation details. If you don't provide a table ID, the
 *                                                  system automatically creates one.
 * @param string $tableId                           The ID of the BigQuery table where you want to store the
 *                                                  transformation details.
 */
function deidentify_cloud_storage(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $inputgcsPath = 'gs://YOUR_GOOGLE_STORAGE_BUCKET',
    string $outgcsPath = 'gs://YOUR_GOOGLE_STORAGE_BUCKET',
    string $deidentifyTemplateName = 'YOUR_DEIDENTIFY_TEMPLATE_NAME',
    string $structuredDeidentifyTemplateName = 'YOUR_STRUCTURED_DEIDENTIFY_TEMPLATE_NAME',
    string $imageRedactTemplateName = 'YOUR_IMAGE_REDACT_DEIDENTIFY_TEMPLATE_NAME',
    string $datasetId = 'YOUR_DATASET_ID',
    string $tableId = 'YOUR_TABLE_ID',
    DlpServiceClient $serviceClient = null
): void {
    // Instantiate a client.
    $dlp = $serviceClient ?? new DlpServiceClient();

    $parent = "projects/$callingProjectId/locations/global";

    // Specify the GCS Path to be de-identify.
    $cloudStorageOptions = (new CloudStorageOptions())
        ->setFileSet((new FileSet())
            ->setUrl($inputgcsPath));
    $storageConfig = (new StorageConfig())
        ->setCloudStorageOptions(($cloudStorageOptions));

    // Specify the type of info the inspection will look for.
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([
            (new InfoType())->setName('PERSON_NAME'),
            (new InfoType())->setName('EMAIL_ADDRESS')
        ]);

    // Specify the big query table to store the transformation details.
    $transformationDetailsStorageConfig = (new TransformationDetailsStorageConfig())
        ->setTable((new BigQueryTable())
            ->setProjectId($callingProjectId)
            ->setDatasetId($datasetId)
            ->setTableId($tableId));

    // Specify the de-identify template used for the transformation.
    $transformationConfig = (new TransformationConfig())
        ->setDeidentifyTemplate(
            DlpServiceBaseClient::projectDeidentifyTemplateName($callingProjectId, $deidentifyTemplateName)
        )
        ->setStructuredDeidentifyTemplate(
            DlpServiceBaseClient::projectDeidentifyTemplateName($callingProjectId, $structuredDeidentifyTemplateName)
        )
        ->setImageRedactTemplate(
            DlpServiceBaseClient::projectDeidentifyTemplateName($callingProjectId, $imageRedactTemplateName)
        );

    $deidentify = (new Deidentify())
        ->setCloudStorageOutput($outgcsPath)
        ->setTransformationConfig($transformationConfig)
        ->setTransformationDetailsStorageConfig($transformationDetailsStorageConfig)
        ->setFileTypesToTransform([FileType::TEXT_FILE, FileType::IMAGE, FileType::CSV]);

    $action = (new Action())
        ->setDeidentify($deidentify);

    // Configure the inspection job we want the service to perform.
    $inspectJobConfig = (new InspectJobConfig())
        ->setInspectConfig($inspectConfig)
        ->setStorageConfig($storageConfig)
        ->setActions([$action]);

    // Send the job creation request and process the response.
    $job = $dlp->createDlpJob($parent, [
        'inspectJob' => $inspectJobConfig
    ]);

    $numOfAttempts = 10;
    do {
        printf('Waiting for job to complete' . PHP_EOL);
        sleep(30);
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
# [END dlp_deidentify_cloud_storage]
// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
