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

# [START documentai_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\DocumentAI\V1\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\ProcessRequest;
use Google\Cloud\DocumentAI\V1\RawDocument;

# Create Client
$client = new DocumentProcessorServiceClient();

# Local File Path
$documentPath = "resources/invoice.pdf";

# Read in File Contents
$handle = fopen($documentPath, "rb");
$contents = fread($handle, filesize($documentPath));
fclose($handle);

# Load File Contents into RawDocument
$rawDocument = new RawDocument([
    "content" => $contents,
    "mime_type" => "application/pdf"
]);

# Fully-qualified Processor Name
$name = $client.processorName($project, $location, $processor)

$processRequest = new ProcessRequest([
    "name" => $name,
    "raw_document" => $rawDocument
]);

# Make Processing Request
$response = $documentProcessorServiceClient->processDocument($processRequest);

# Print Document Text
printf($response->$document.$text)

# [END documentai_quickstart]
