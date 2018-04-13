<?php
/**
 * Copyright 2018 Google LLC
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

# [START bigquerydatatransfer_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\BigQuery\DataTransfer\V1\DataTransferServiceClient;

# Instantiates a client
$bqdtsClient = new DataTransferServiceClient();

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';
$parent = sprintf('projects/%s/locations/us', $projectId);

try {
    echo 'Supported Data Sources:', PHP_EOL;
    $pagedResponse = $bqdtsClient->listDataSources($parent);
    foreach ($pagedResponse->iterateAllElements() as $dataSource) {
        echo 'Data source: ', $dataSource->getDisplayName(), PHP_EOL;
        echo 'ID: ', $dataSource->getDataSourceId(), PHP_EOL;
        echo 'Full path: ', $dataSource->getName(), PHP_EOL;
        echo 'Description: ', $dataSource->getDescription(), PHP_EOL;
    }
} finally {
    $bqdtsClient->close();
}
# [END bigquerydatatransfer_quickstart]
