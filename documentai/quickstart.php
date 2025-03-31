<?php
/**
 * Copyright 2023 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

# [START documentai_quickstart]
# Include the autoloader for libraries installed with Composer.
require __DIR__ . '/vendor/autoload.php';

# Import the Google Cloud client library.
use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\RawDocument;
use Google\Cloud\DocumentAI\V1\ProcessRequest;

# TODO(developer): Update the following lines before running the sample.
# Your Google Cloud Platform project ID.
$projectId = 'YOUR_PROJECT_ID';

# Your Processor Location.
$location = 'us';

# Your Processor ID as hexadecimal characters.
# Not to be confused with the Processor Display Name.
$processorId = 'YOUR_PROCESSOR_ID';

# Path for the file to read.
$documentPath = 'resources/invoice.pdf';

# Create Client.
$client = new DocumentProcessorServiceClient();

# Read in file.
$handle = fopen($documentPath, 'rb');
$contents = fread($handle, filesize($documentPath));
fclose($handle);

# Load file contents into a RawDocument.
$rawDocument = new RawDocument()
    ->setContent($contents)
    ->SetMimeType('application/pdf');

# Get the Fully-qualified Processor Name.
$fullProcessorName = $client->processorName($projectId, $location, $processorId);

# Send a ProcessRequest and get a ProcessResponse.
$request = new ProcessRequest()
    ->setName($fullProcessorName)
    ->setRawDocument($rawDocument);

$response = $client->processDocument($request);

# Show the text found in the document.
printf('Document Text: %s', $response->getDocument()->getText());
# [END documentai_quickstart]
