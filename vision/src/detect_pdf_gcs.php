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

// [START vision_async_detect_document_ocr]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\AsyncAnnotateFileRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature_Type;
use Google\Cloud\Vision\V1\GcsSource;
use Google\Cloud\Vision\V1\InputConfig;
use Google\Cloud\Vision\V1\GcsDestination;
use Google\Cloud\Vision\V1\OutputConfig;
use Google\Cloud\Storage\StorageClient;

// $path = 'gs://path/to/your/document.pdf'

function detect_pdf_gcs($path, $output)
{
    $mimeType = 'application/pdf';
    $batchSize = 2;
    
    $feature = new Feature();
    $feature->setType(Feature_Type::DOCUMENT_TEXT_DETECTION);
    $features = [$feature];

    $gcsSource = new GcsSource();
    $gcsSource->setUri($path);
    $inputConfig = new InputConfig();
    $inputConfig->setGcsSource($gcsSource);
    $inputConfig->setMimeType($mimeType);

    $gcsDestination = new GcsDestination();
    $gcsDestination->setUri($output);
    $outputConfig = new OutputConfig();
    $outputConfig->setGcsDestination($gcsDestination);
    $outputConfig->setBatchSize($batchSize);

    $request = new AsyncAnnotateFileRequest();
    $request->setFeatures($features);
    $request->setInputConfig($inputConfig);
    $request->setOutputConfig($outputConfig);
    $requests = [$request];

    $imageAnnotator = new ImageAnnotatorClient();
    $operation = $imageAnnotator->asyncBatchAnnotateFiles($requests);
    print('Waiting for operation to finish.' . PHP_EOL);
    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        $result = $operation->getResult();
        print($result->getMessage());
    } else {
        $error = $operation->getError();
        print($error->getMessage());
    }

    // $storage = new StorageClient();
    // $test = preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $output, $match);
    // $bucketName = $match[1];
    // $prefix = $match[2];

    // $imageAnnotator->close();
}
// [END vision_async_detect_document_ocr]
