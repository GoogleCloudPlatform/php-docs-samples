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

# [START dlp_deidentify_table_fpe]

use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CryptoKey;
use Google\Cloud\Dlp\V2\CryptoReplaceFfxFpeConfig;
use Google\Cloud\Dlp\V2\CryptoReplaceFfxFpeConfig\FfxCommonNativeAlphabet;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\DeidentifyContentRequest;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\Dlp\V2\FieldTransformation;
use Google\Cloud\Dlp\V2\KmsWrappedCryptoKey;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\RecordTransformations;
use Google\Cloud\Dlp\V2\Table;
use Google\Cloud\Dlp\V2\Table\Row;
use Google\Cloud\Dlp\V2\Value;

/**
 * De-identify table data with format-preserving encryption.
 * Demonstrates encrypting sensitive data in a table while maintaining format.
 *

 * @param string $callingProjectId      The Google Cloud project id to use as a parent resource.
 * @param string $inputCsvFile          The input file(csv) path  to deidentify.
 * @param string $outputCsvFile         The oupt file path to save deidentify content.
 * @param string $encryptedFieldNames   The field to be encrypted.
 * @param string $kmsKeyName            The name of the Cloud KMS key used to encrypt ('wrap') the AES-256 key.
 * Example: key_name = 'projects/YOUR_GCLOUD_PROJECT/locations/YOUR_LOCATION/keyRings/YOUR_KEYRING_NAME/cryptoKeys/YOUR_KEY_NAME'
 * @param string $wrappedAesKey         The encrypted ('wrapped') AES-256 key to use.

 * */

function deidentify_table_fpe(
    string $callingProjectId,
    string $inputCsvFile,
    string $outputCsvFile,
    string $encryptedFieldNames,
    string $kmsKeyName,
    string $wrappedAesKey
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

    // Specify the content to be de-identify.
    $content = (new ContentItem())
        ->setTable($tableToDeIdentify);

    // Specify an encrypted AES-256 key and the name of the Cloud KMS key that encrypted it.
    $kmsWrappedCryptoKey = (new KmsWrappedCryptoKey())
        ->setWrappedKey(base64_decode($wrappedAesKey))
        ->setCryptoKeyName($kmsKeyName);

    $cryptoKey = (new CryptoKey())
        ->setKmsWrapped($kmsWrappedCryptoKey);

    // Specify how the content should be encrypted.
    $cryptoReplaceFfxFpeConfig = (new CryptoReplaceFfxFpeConfig())
        ->setCryptoKey($cryptoKey)
        ->setCommonAlphabet(FfxCommonNativeAlphabet::NUMERIC);

    $primitiveTransformation = (new PrimitiveTransformation())
        ->setCryptoReplaceFfxFpeConfig($cryptoReplaceFfxFpeConfig);

    // Specify field to be encrypted.
    $encryptedFields = array_map(function ($encryptedFieldName) {
        return (new FieldId())
            ->setName($encryptedFieldName);
    }, explode(',', $encryptedFieldNames));

    // Associate the encryption with the specified field.
    $fieldTransformation = (new FieldTransformation())
        ->setPrimitiveTransformation($primitiveTransformation)
        ->setFields($encryptedFields);

    $recordtransformations = (new RecordTransformations())
        ->setFieldTransformations([$fieldTransformation]);

    $deidentifyConfig = (new DeidentifyConfig())
        ->setRecordTransformations($recordtransformations);

    // Run request.
    $deidentifyContentRequest = (new DeidentifyContentRequest())
        ->setParent($parent)
        ->setDeidentifyConfig($deidentifyConfig)
        ->setItem($content);
    $response = $dlp->deidentifyContent($deidentifyContentRequest);

    // Print the results.
    $csvRef = fopen($outputCsvFile, 'w');
    fputcsv($csvRef, $csvHeaders);
    foreach ($response->getItem()->getTable()->getRows() as $tableRow) {
        $values = array_map(function ($tableValue) {
            return $tableValue->getStringValue();
        }, iterator_to_array($tableRow->getValues()));
        fputcsv($csvRef, $values);
    };
    printf('Table after format-preserving encryption (File Location): %s', $outputCsvFile);
}
# [END dlp_deidentify_table_fpe]

// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
