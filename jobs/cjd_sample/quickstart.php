<?php
/**
 * Copyright 2017 Google Inc.
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

# [START quickstart]
// Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

// Instantiate the client
$client = new Google_Client();

// Authorize the client using Application Default Credentials
// @see https://developers.google.com/identity/protocols/application-default-credentials
$client->useApplicationDefaultCredentials();
$client->setScopes(array(
    'https://www.googleapis.com/auth/jobs'
));

// Instantiate the Cloud Job Discovery Service API
$cloudTalentSolutionClient = new Google_Service_CloudTalentSolution($client);
$projectId = getenv('GOOGLE_PROJECT_ID');
$parent = sprintf('projects/%s', $projectId);

// list companies
$companies = $cloudTalentSolutionClient->projects_companies->listProjectsCompanies(
	$projectId,
    array('parent' => $parent)
);

// Print the companies
echo 'Companies: ' . PHP_EOL;
foreach ($companies as $company) {
    echo $company->name . PHP_EOL;
}
# [END quickstart]
return $companies;