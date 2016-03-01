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
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Create an authorized client that we will use to invoke BigQuery.
 *
 * @return Google_Service_Bigquery
 *
 * @throws Exception
 */
// [START build_service]
function createAuthorizedClient()
{
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Bigquery::BIGQUERY);

    $service = new Google_Service_Bigquery($client);

    return $service;
}
// [END build_service]

/**
 * Get all the rows, page by page, for a given job.
 *
 * @return Generator
 */
// [START paging]
function getRows(
    Google_Service_Bigquery $bigquery,
    $projectId,
    $jobId,
    $rowsPerPage = null)
{
    $pageToken = null;
    do {
        $page = $bigquery->jobs->getQueryResults($projectId, $jobId, array(
            'pageToken' => $pageToken,
            'maxResults' => $rowsPerPage,
        ));
        $rows = $page->getRows();
        if ($rows) {
            foreach ($rows as $row) {
                yield $row;
            }
        }
        $pageToken = $page->getPageToken();
    } while ($pageToken);
}
// [END paging]

/**
 * Use the sychronous API to execute a query.  Returns null if job timed out.
 *
 * @return array|null
 */
// [START sync_query]
function syncQuery(
    Google_Service_Bigquery $bigquery,
    $projectId,
    $queryString,
    $timeout = 10000)
{
    $request = new Google_Service_Bigquery_QueryRequest();
    $request->setQuery($queryString);
    $request->setTimeoutMs($timeout);
    $response = $bigquery->jobs->query($projectId, $request);
    if (!$response->getJobComplete()) {
        return;
    }

    return $response->getRows() ? $response->getRows() : array();
}
// [END sync_query]

/**
 * Use the asynchronous API to execute a query.
 *
 * @return Google_Service_Bigquery_Job
 */
// [START async_query]
function asyncQuery(
    Google_Service_Bigquery $bigquery,
    $projectId,
    $queryString,
    $batch = false)
{
    $query = new Google_Service_Bigquery_JobConfigurationQuery();
    $query->setQuery($queryString);
    $query->setPriority($batch ? 'BATCH' : 'INTERACTIVE');
    $config = new Google_Service_Bigquery_JobConfiguration();
    $config->setQuery($query);
    $job = new Google_Service_Bigquery_Job();
    $job->setConfiguration($config);

    return $bigquery->jobs->insert($projectId, $job);
}
// [END async_query]

/**
 * Wait until a job completes.
 *
 * @param Google_Service_Bigquery $bigquery
 * @param $projectId
 * @param $jobId
 * @param $intervalMs  How long should we sleep between checks?
 *
 * @return Google_Service_Bigquery_Job
 */
// [START poll_job]
function pollJob(
    Google_Service_Bigquery $bigquery,
    $projectId,
    $jobId,
    $intervalMs)
{
    while (true) {
        $job = $bigquery->jobs->get($projectId, $jobId);
        if ($job->getStatus()->getState() == 'DONE') {
            return $job;
        }
        usleep(1000 * $intervalMs);
    }
}
// [END poll_job]

/**
 * List the datasets.  Never return null.
 *
 * @param Google_Service_Bigquery $bigquery
 * @param $projectId
 *
 * @return array
 */
// [START list_datasets]
function listDatasets(Google_Service_Bigquery $bigquery, $projectId)
{
    $datasets = $bigquery->datasets->listDatasets($projectId);
    // Never return null.
    return $datasets->getDatasets() ? $datasets->getDatasets() : array();
}
// [END list_datasets]

/**
 * List the projects.  Never return null.
 *
 * @param Google_Service_Bigquery $bigquery
 *
 * @return array
 */
// [START list_projects]
function listProjects(Google_Service_Bigquery $bigquery)
{
    $projects = $bigquery->projects->listProjects();
    // Never return null.
    return $projects->getProjects() ? $projects->getProjects() : array();
}
// [END list_projects]
