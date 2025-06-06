<?php
/*
 * Copyright 2025 Google LLC.
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

declare(strict_types=1);

namespace Google\Cloud\Samples\ModelArmor;

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 5) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID TEMPLATE_ID PDF_FILE_NAME\n", basename(__FILE__));
}
list($_, $projectId, $locationId, $templateId, $filePath) = $argv;

// [START modelarmor_screen_pdf_file]
// Import the ModelArmor client library.
use Google\Cloud\ModelArmor\V1\CLient\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\SanitizeUserPromptRequest;
use Google\Cloud\ModelArmor\V1\ByteDataItem;
use Google\Cloud\ModelArmor\V1\ByteDataItem\ByteItemType;
use Google\Cloud\ModelArmor\V1\DataItem;

/** Uncomment and populate these variables in your code. */
// $projectId = "YOUR_GOOGLE_CLOUD_PROJECT"; // e.g. 'my-project';
// $locationId = 'YOUR_LOCATION_ID'; // e.g. 'us-central1';
// $templateId = 'YOUR_TEMPLATE_ID'; // e.g. 'my-template';
// $userPrompt = 'YOUR_USER_PROMPT'; // e.g. 'my-user-prompt';
// $filePath = 'YOUR_PDF_FILE_NAME'; // e.g. ''path/to/file.pdf';

// Specify regional endpoint.
$options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];

// Instantiates a client.
$client = new ModelArmorClient($options);

// Read the file content and encode it in base64.
$pdfContent = file_get_contents($filePath);
$pdfContentBase64 = base64_encode($pdfContent);

$userPromptRequest = (new SanitizeUserPromptRequest())
    ->setName("projects/$projectId/locations/$locationId/templates/$templateId")
    ->setUserPromptData((new DataItem())
        ->setByteItem(new ByteDataItem()->setByteData($pdfContentBase64)
            ->setByteDataType(ByteItemType::PDF)));

$response = $client->sanitizeUserPrompt($userPromptRequest);

printf('Result for Screen PDF File: %s' . PHP_EOL, $response->serializeToJsonString());
// [END modelarmor_screen_pdf_file]
