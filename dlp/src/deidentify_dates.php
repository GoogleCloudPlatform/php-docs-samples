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

# [START deidentify_dates]
use Google\Cloud\Dlp\V2\CharacterMaskConfig;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\DateShiftConfig;
use Google\Cloud\Dlp\V2\InfoTypeTransformations_InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\FieldTransformation;
use Google\Cloud\Dlp\V2\RecordTransformation;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\Dlp\V2\Table;
use Google\Cloud\Dlp\V2\CryptoKey;
use Google\Cloud\Dlp\V2\KmsWrappedCryptoKey;
use Google\Cloud\Dlp\V2\RecordTransformations;
use Google\Cloud\Dlp\V2\DateTime;
use Google\Cloud\Dlp\V2\Table_Row;
use Google\Cloud\Dlp\V2\Value;
use Google\Type\Date;

/**
 * Deidentify dates in a CSV file by pseudorandomly shifting them.
 * 
 * @param string $callingProject The GCP Project ID to run the API call under
 * @param string $inputCsvFile The path to the CSV file to deidentify
 * @param string $outputCsvFile The path to save the date-shifted CSV file to
 * @param array $dateFieldNames The list of (date) fields in the CSV file to date shift
 * @param string $lowerBoundDays The maximum number of days to shift a date backward
 * @param string $upperBoundDays The maximum number of days to shift a date forward
 * @param string contextFieldName optional The column to determine date shift amount based on
 *        If this is not specified, a random shift amount will be used for every row.
 *        If this is specified, then 'wrappedKey' and 'keyName' must also be set
 * @param string keyName optional The encrypted ('wrapped') AES-256 key to use when shifting dates
 *        If this is specified, then 'wrappedKey' and 'contextFieldName' must also be set
 * @param string wrappedKey optional The name of the Cloud KMS key used to encrypt ('wrap') the AES-256 key
 *        If this is specified, then 'keyName' and 'contextFieldName' must also be set
 */
function deidentify_dates(
    $callingProjectId,
    $inputCsvFile,
    $outputCsvFile,
    $dateFieldNames,
    $lowerBoundDays,
    $upperBoundDays,
    $contextFieldName,
    $keyName,
    $wrappedKey
) {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Read a CSV file
    $csvLines = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
    $csvHeaders = explode(',', $csvLines[0]);
    $csvRows = array_slice($csvLines, 1);

    // Convert CSV file into protobuf objects
    $tableHeaders = array_map(function ($csvHeader) {
        return (new FieldId)->setName($csvHeader);
    }, $csvHeaders);

    $tableRows = array_map(function ($csvRow) {
        $rowValues = array_map(function ($csvValue) {
            if ($csvDate = strptime($csvValue, '%m/%d/%Y')) {
                $date = new Date();
                $date->setYear((int) $csvDate['tm_year']);
                $date->setMonth((int) $csvDate['tm_mon']);
                $date->setDay((int) $csvDate['tm_mday']);

                $protoDate = new Value();
                $protoDate->setDateValue($date);
                return $protoDate;
            } else {
                $protoString = new Value();
                $protoString->setStringValue($csvValue);
                //var_dump($protoString->getStringValue());
                return $protoString;
            }
        }, explode(',', $csvRow));

        $tableRow = new Table_Row();
        $tableRow->setValues($rowValues);
        return $tableRow;
    }, $csvRows);

    // Convert date fields into protobuf objects
    $dateFields = array_map(function($dateFieldName) {
        return (new FieldId())->setName($dateFieldName);
    }, $dateFieldNames);

    // Construct the table object
    $table = new Table();
    $table->setHeaders($tableHeaders);
    $table->setRows($tableRows);

    $item = new ContentItem();
    //$item->setType('text/csv');
    $item->setTable($table);

    // Construct dateShiftConfig
    $dateShiftConfig = new DateShiftConfig();
    $dateShiftConfig->setLowerBoundDays($lowerBoundDays);
    $dateShiftConfig->setUpperBoundDays($upperBoundDays);

    if ($contextFieldName && $keyName && $wrappedKey) {
        $contextField = new FieldId();
        $contextField->setName($contextFieldName);

        // Create the wrapped crypto key configuration object
        $kmsWrappedCryptoKey = new KmsWrappedCryptoKey();
        $kmsWrappedCryptoKey->setWrappedKey(base64_decode($wrappedKey));
        $kmsWrappedCryptoKey->setCryptoKeyName($keyName);

        $cryptoKey = new CryptoKey();
        $cryptoKey->setKmsWrapped($kmsWrappedCryptoKey);

        $dateShiftConfig->setContext($contextField);
        $dateShiftConfig->setCryptoKey($cryptoKey);
    } else if ($contextFieldName || $keyName || $wrappedKey) {
        throw new Exception('You must set either ALL or NONE of {$contextFieldName, $keyName, $wrappedKey}!');
    }

    // Create the information transform configuration objects
    $primitiveTransformation = new PrimitiveTransformation();
    $primitiveTransformation->setDateShiftConfig($dateShiftConfig);

    $fieldTransformation = new FieldTransformation();
    $fieldTransformation->setPrimitiveTransformation($primitiveTransformation);
    $fieldTransformation->setFields($dateFields);

    $recordTransformations = new RecordTransformations();
    $recordTransformations->setFieldTransformations([$fieldTransformation]);

    // Create the deidentification configuration object
    $deidentifyConfig = new DeidentifyConfig();
    $deidentifyConfig->setRecordTransformations($recordTransformations);

    $parent = $dlp->projectName($callingProjectId);

    // Run request
    $response = $dlp->deidentifyContent($parent, Array(
        'deidentifyConfig' => $deidentifyConfig,
        'item' => $item
    ));

    // Save the results to a file
    $resultTable = $response->getItem()->getTable();
    $outputCsvText = join($csvHeaders, ',') . PHP_EOL;
    foreach ($resultTable->getRows() as $tableRow) {
        foreach ($tableRow->getValues() as $tableValueIdx => $tableValue) {
            if ($tableValueIdx != 0) {
                $outputCsvText .= ',';
            }

            if ($tableValue->getStringValue()) {
                $outputCsvText .= $tableValue->getStringValue();
            }
            else {
                $protoDate = $tableValue->getDateValue();
                $date = mktime(0, 0, 0, $protoDate->getMonth(), $protoDate->getDay(), $protoDate->getYear());
                $outputCsvText .= strftime('%D', $date);
            }
        };
        $outputCsvText .= PHP_EOL;
    };
    file_put_contents($outputCsvFile, $outputCsvText);
}
# [END deidentify_dates]
