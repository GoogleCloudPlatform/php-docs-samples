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

# [START inspect_bigquery]
use Google\Cloud\Dlp\V2beta1\DlpServiceClient;
use Google\Privacy\Dlp\V2beta1\BigQueryOptions;
use Google\Privacy\Dlp\V2beta1\InfoType;
use Google\Privacy\Dlp\V2beta1\InspectConfig;
use Google\Privacy\Dlp\V2beta1\StorageConfig;
use Google\Privacy\Dlp\V2beta1\BigQueryTable;
use Google\Privacy\Dlp\V2beta1\Likelihood;

/**
 * Inspect a BigQuery table using the Data Loss Prevention (DLP) API.
 *
 * @param string $datasetId The ID of the dataset to inspect.
 * @param string $tableId The ID of the table to inspect.
 * @param string $projectId Optional The project ID containing the target BigQuery table.
 */
function inspect_bigquery(
    $datasetId,
    $tableId,
    $projectId = '',
    $minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED,
    $maxFindings = 0)
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // The infoTypes of information to match
    $emailAddressInfoType = new InfoType();
    $emailAddressInfoType->setName('EMAIL_ADDRESS');
    $creditCardNumberInfoType = new InfoType();
    $creditCardNumberInfoType->setName('CREDIT_CARD_NUMBER');
    $infoTypes = [$emailAddressInfoType, $creditCardNumberInfoType];

    // Create the configuration object
    $inspectConfig = new InspectConfig();
    $inspectConfig->setMinLikelihood($minLikelihood);
    $inspectConfig->setMaxFindings($maxFindings);
    $inspectConfig->setInfoTypes($infoTypes);

    $tableReference = new BigQueryTable();
    $tableReference->setProjectId($projectId);
    $tableReference->setDatasetId($datasetId);
    $tableReference->setTableId($tableId);

    $bigQueryOptions = new BigQueryOptions();
    $bigQueryOptions->setTableReference($tableReference);

    $storageConfig = new StorageConfig();
    $storageConfig->setBigQueryOptions($bigQueryOptions);

    $outputConfig = null;

    // Run request
    $operation = $dlp->createInspectOperation(
        $inspectConfig,
        $storageConfig,
        $outputConfig);

    $operation->pollUntilComplete();

    if ($operation->operationSucceeded()) {
        $result = $operation->getResult();
        $response = $dlp->listInspectFindings($result->getName());

        $likelihoods = ['Unknown', 'Very unlikely', 'Unlikely', 'Possible',
                        'Likely', 'Very likely'];

        // Print the results
        $findings = $response->getResult()->getFindings();
        if (count($findings) == 0) {
            print('No findings.' . PHP_EOL);
        } else {
            print('Findings:' . PHP_EOL);
            foreach ($findings as $finding) {
                printf('- Info type: %s' . PHP_EOL,
                    $finding->getInfoType()->getName());
                printf('  Likelihood: %s' . PHP_EOL,
                    $likelihoods[$finding->getLikelihood()]);
            }
        }
    } else {
        print_r($operation->getError());
    }
}
# [END inspect_bigquery]
