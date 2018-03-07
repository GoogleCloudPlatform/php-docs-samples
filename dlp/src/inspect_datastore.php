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

# [START dlp_inspect_datastore]
use Google\Cloud\Dlp\V2beta1\DlpServiceClient;
use Google\Cloud\Dlp\V2beta1\DatastoreOptions;
use Google\Cloud\Dlp\V2beta1\InfoType;
use Google\Cloud\Dlp\V2beta1\InspectConfig;
use Google\Cloud\Dlp\V2beta1\KindExpression;
use Google\Cloud\Dlp\V2beta1\PartitionId;
use Google\Cloud\Dlp\V2beta1\StorageConfig;
use Google\Cloud\Dlp\V2beta1\Likelihood;

/**
 * Inspect Datastore using the Data Loss Prevention (DLP) API.
 *
 * @param string $kind Optional The datastore kind to inspect
 * @param string $namespaceId Optional The ID namespace of the Datastore document to inspect.
 * @param string $projectId Optional The project ID containing the target Datastore.
 */
function inspect_datastore(
    $kind,
    $namespaceId = '',
    $projectId = '',
    $minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED,
    $maxFindings = 0)
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // The infoTypes of information to match
    $usMaleNameInfoType = new InfoType();
    $usMaleNameInfoType->setName('US_MALE_NAME');
    $usFemaleNameInfoType = new InfoType();
    $usFemaleNameInfoType->setName('US_FEMALE_NAME');
    $infoTypes = [$usMaleNameInfoType, $usFemaleNameInfoType];

    // Create the configuration object
    $inspectConfig = new InspectConfig();
    $inspectConfig->setMinLikelihood($minLikelihood);
    $inspectConfig->setMaxFindings($maxFindings);
    $inspectConfig->setInfoTypes($infoTypes);

    $partitionId = new PartitionId();
    $partitionId->setProjectId($projectId);
    $partitionId->setNamespaceId($namespaceId);

    $kindExpression = new KindExpression();
    $kindExpression->setName($kind);

    $datastoreOptions = new DatastoreOptions();
    $datastoreOptions->setPartitionId($partitionId);
    $datastoreOptions->setKind($kindExpression);

    $storageConfig = new StorageConfig();
    $storageConfig->setDatastoreOptions($datastoreOptions);

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
# [END dlp_inspect_datastore]
