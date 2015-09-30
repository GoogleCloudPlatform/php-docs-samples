<?php
// Copyright 2015 Google Inc. All Rights Reserved.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

require_once __DIR__ . '/vendor/autoload.php';
// [START all]
// [START build_service]
/**
 * Create an authorized client that we will use to invoke BigQuery.
 * @return Google_Service_Bigquery
 * @throws Exception
 */
function createAuthorizedClient()
{
    $json_credentials_path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
    if (!$json_credentials_path) {
        throw new Exception('Set the environment variable ' .
            'GOOGLE_APPLICATION_CREDENTIALS to the path to your .json file.');
    }
    $contents = file_get_contents($json_credentials_path);
    $json_array = json_decode($contents, true);
    $credentials = new Google_Auth_AssertionCredentials(
        $json_array['client_email'],
        [Google_Service_Bigquery::BIGQUERY],
        $json_array['private_key']
    );
    $client = new Google_Client();
    $client->setAssertionCredentials($credentials);
    if ($client->getAuth()->isAccessTokenExpired()) {
        $client->getAuth()->refreshTokenWithAssertion();
    }
    $service = new Google_Service_Bigquery($client);
    return $service;
}
// [END build_service]
$bigquery = createAuthorizedClient();
$projectId = '';
if ($projectId) {
    // The programmer already set the projectId above.
} elseif ($argc > 1) {
    $projectId = $argv[1];
} else {
    echo "Enter the project ID: ";
    $projectId = trim(fgets(STDIN));
}

// [START run_query]
// Pack a BigQuery request.
$request = new Google_Service_Bigquery_QueryRequest();
$request->setQuery('SELECT TOP(corpus, 10) as title, COUNT(*) as unique_words ' .
    'FROM [publicdata:samples.shakespeare]');
$response = $bigquery->jobs->query($projectId, $request);
$rows = $response->getRows();
// [END run_query]

// [START print_results]
// Print the results to stdout in a human-readable way.
echo "\nQuery Results:\n------------\n";
foreach ($rows as $row) {
    foreach ($row['f'] as $field) {
        printf('%-30s', $field['v']);
    }
    echo "\n";
}
// [END print_results]
// [END all]