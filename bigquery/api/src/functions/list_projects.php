<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigquery/api/README.md
 */

namespace Google\Cloud\Samples\BigQuery;

# [START list_projects]
use Google\Auth\CredentialsLoader;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\Connection\Rest;

function list_projects()
{
    $keyFile = CredentialsLoader::fromWellKnownFile();
    $scopes = BigQueryClient::SCOPE;
    $connection = new Rest([
        'scopes' => $scopes,
        'keyFile' => $keyFile,
    ]);
    $result = $connection->send('projects', 'list');
    foreach ($result['projects'] as $project) {
        printf($project['id'] . PHP_EOL);
    }
}
# [END list_projects]
