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

# [START dlp_deidentify_table_with_multiple_crypto_hash]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CryptoHashConfig;
use Google\Cloud\Dlp\V2\CryptoKey;
use Google\Cloud\Dlp\V2\Value;
use Google\Cloud\Dlp\V2\Table;
use Google\Cloud\Dlp\V2\Table\Row;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\Dlp\V2\FieldTransformation;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\RecordTransformations;
use Google\Cloud\Dlp\V2\TransientCryptoKey;

/**
 * De-identify table data with multiple crypto hash.
 * Transform findings using two separate cryptographic hash transformations.
 *
 * @param string $callingProjectId          The Google Cloud project id to use as a parent resource.
 * @param string $inputCsvFile              The input file(csv) path  to deidentify.
 * @param string $outputCsvFile             The oupt file path to save deidentify content.
 * @param string $transientCryptoKeyName1   Specify the random string.
 * @param string $transientCryptoKeyName2   Specify the random string.
 */

function deidentify_table_with_multiple_crypto_hash(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $inputCsvFile = './test/data/table6.csv',
    string $outputCsvFile = './test/data/deidentify_table_with_multiple_crypto_hash_output.csv',
    string $transientCryptoKeyName1 = 'YOUR-TRANSIENT-CRYPTO-KEY-1',
    string $transientCryptoKeyName2 = 'YOUR-TRANSIENT-CRYPTO-KEY-2'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$callingProjectId/locations/global";

    // Read a CSV file.
    $csvLines = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
    $csvHeaders = explode(',', $csvLines[0]);
    $csvRows = array_slice($csvLines, 1);

    // Convert CSV file into protobuf objects.
    $tableHeaders = array_map(function ($csvHeader) {
        return (new FieldId)
            ->setName($csvHeader);
    }, $csvHeaders);

    $tableRows = array_map(function ($csvRow) {
        $rowValues = array_map(function ($csvValue) {
            return (new Value())
                ->setStringValue($csvValue);
        }, explode(',', $csvRow));
        return (new Row())
            ->setValues($rowValues);
    }, $csvRows);

    // Construct the table object.
    $tableToDeIdentify = (new Table())
        ->setHeaders($tableHeaders)
        ->setRows($tableRows);

    // Specify what content you want the service to de-identify.
    $content = (new ContentItem())
        ->setTable($tableToDeIdentify);

    // Specify the type of info the inspection will look for.
    // See https://cloud.google.com/dlp/docs/infotypes-reference for complete list of info types
    $infoTypes = [
        (new InfoType())->setName('EMAIL_ADDRESS'),
        (new InfoType())->setName('PHONE_NUMBER')
    ];

    $inspectConfig = (new InspectConfig())
        ->setInfoTypes($infoTypes);

    // ---- First Crypto Hash Rule ----

    // Specify the transient key which will encrypt the data.
    $cryptoHashConfig1 = (new CryptoHashConfig())
        ->setCryptoKey((new CryptoKey())
            ->setTransient((new TransientCryptoKey())
                ->setName($transientCryptoKeyName1)));

    // Define type of de-identification as cryptographic hash transformation.
    $primitiveTransformation1 = (new PrimitiveTransformation())
        ->setCryptoHashConfig($cryptoHashConfig1);

    $fieldTransformation1 = (new FieldTransformation())
        ->setPrimitiveTransformation($primitiveTransformation1)
        // Specify fields to be de-identified.
        ->setFields([
            (new FieldId())->setName('userid')
        ]);

    // ---- Second Crypto Hash Rule ----

    // Specify the transient key which will encrypt the data.
    $cryptoHashConfig2 = (new CryptoHashConfig())
        ->setCryptoKey((new CryptoKey())
            ->setTransient((new TransientCryptoKey())
                ->setName($transientCryptoKeyName2)));

    // Define type of de-identification as cryptographic hash transformation.
    $primitiveTransformation2 = (new PrimitiveTransformation())
        ->setCryptoHashConfig($cryptoHashConfig2);

    $infoTypeTransformation = (new InfoTypeTransformation())
        ->setPrimitiveTransformation($primitiveTransformation2)
        ->setInfoTypes($infoTypes);

    $infoTypeTransformations = (new InfoTypeTransformations())
        ->setTransformations([$infoTypeTransformation]);

    $fieldTransformation2 = (new FieldTransformation())
        ->setInfoTypeTransformations($infoTypeTransformations)
        // Specify fields to be de-identified.
        ->setFields([
            (new FieldId())->setName('comments')
        ]);

    $recordtransformations = (new RecordTransformations())
        ->setFieldTransformations([$fieldTransformation1, $fieldTransformation2]);

    // Specify the config for the de-identify request
    $deidentifyConfig = (new DeidentifyConfig())
        ->setRecordTransformations($recordtransformations);

    // Send the request and receive response from the service.
    $response = $dlp->deidentifyContent([
        'parent' => $parent,
        'inspectConfig' => $inspectConfig,
        'deidentifyConfig' => $deidentifyConfig,
        'item' => $content
    ]);

    // Print the results.
    $csvRef = fopen($outputCsvFile, 'w');
    fputcsv($csvRef, $csvHeaders);
    foreach ($response->getItem()->getTable()->getRows() as $tableRow) {
        $values = array_map(function ($tableValue) {
            return $tableValue->getStringValue();
        }, iterator_to_array($tableRow->getValues()));
        fputcsv($csvRef, $values);
    };
    printf('Table after deidentify (File Location): %s', $outputCsvFile);
}
# [END dlp_deidentify_table_with_multiple_crypto_hash]

// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
