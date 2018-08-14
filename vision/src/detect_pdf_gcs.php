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

// [START vision_text_detection_pdf_gcs]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Vision\V1\AnnotateFileResponse;
use Google\Cloud\Vision\V1\AsyncAnnotateFileRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature_Type;
use Google\Cloud\Vision\V1\GcsDestination;
use Google\Cloud\Vision\V1\GcsSource;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\InputConfig;
use Google\Cloud\Vision\V1\OutputConfig;

// $path = 'gs://path/to/your/document.pdf'

function detect_pdf_gcs($path, $output)
{
    # select ocr feature
    $feature = new Feature();
    $feature->setType(Feature_Type::DOCUMENT_TEXT_DETECTION);
    $features = [$feature];

    # set $path (file to OCR) as source
    $gcsSource = new GcsSource();
    $gcsSource->setUri($path);
    $inputConfig = new InputConfig();
    $inputConfig->setGcsSource($gcsSource);
    # supported mime_types are: 'application/pdf' and 'image/tiff'
    $mimeType = 'application/pdf';
    $inputConfig->setMimeType($mimeType);

    # set $output as destination
    $gcsDestination = new GcsDestination();
    $gcsDestination->setUri($output);
    $outputConfig = new OutputConfig();
    $outputConfig->setGcsDestination($gcsDestination);
    # how many pages should be grouped into each json output file.
    $batchSize = 2;
    $outputConfig->setBatchSize($batchSize);

    # prepare request using configs set above
    $request = new AsyncAnnotateFileRequest();
    $request->setFeatures($features);
    $request->setInputConfig($inputConfig);
    $request->setOutputConfig($outputConfig);
    $requests = [$request];

    # make request
    $imageAnnotator = new ImageAnnotatorClient();
    $operation = $imageAnnotator->asyncBatchAnnotateFiles($requests);
    print('Waiting for operation to finish.' . PHP_EOL);
    $operation->pollUntilComplete();

    # once the request has completed and the output has been
    # written to GCS, we can list all the output files.
    preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $output, $match);
    $bucketName = $match[1];
    $prefix = $match[2];

    # list objects with the given prefix.
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $options = ['prefix' => $prefix];
    print('Output files:' . PHP_EOL);
    $objects = $bucket->objects($options);
    foreach ($objects as $object) {
        print($object->name() . PHP_EOL);
    }

    # process the first output file from GCS.
    # since we specified batch_size=2, the first response contains
    # the first two pages of the input file.
    $jsonString = $objects[0]->downloadAsString();
    $firstBatch = new AnnotateFileResponse();
    $firstBatch->mergeFromJsonString($jsonString);

    # get annotation and print text
    foreach ($firstBatch->getResponses() as $response) {
        $annotation = $response->getFullTextAnnotation();
        print($annotation->getText());
    }

    $imageAnnotator->close();
}
// [END vision_text_detection_pdf_gcs]
