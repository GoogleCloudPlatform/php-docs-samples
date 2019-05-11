<?php
/*
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * DO NOT EDIT! This is a generated sample ("RequestPagedAll",  "job_search_histogram_search")
 */

// [START job_search_histogram_search]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\JobServiceClient;
use Google\Cloud\Talent\V4beta1\HistogramQuery;
use Google\Cloud\Talent\V4beta1\RequestMetadata;

/**
 * Search Jobs with histogram queries.
 *
 * @param string $query Histogram query
 *                      More info on histogram facets, constants, and built-in functions:
 *                      https://godoc.org/google.golang.org/genproto/googleapis/cloud/talent/v4beta1#SearchJobsRequest
 */
function sampleSearchJobs($projectId, $tenantId, $query)
{
    // [START job_search_histogram_search_core]

    $jobServiceClient = new JobServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    // $tenantId = 'Your Tenant ID (using tenancy is optional)';
    // $query = 'count(base_compensation, [bucket(12, 20)])';
    $formattedParent = $jobServiceClient->tenantName($projectId, $tenantId);
    $domain = 'www.example.com';
    $sessionId = 'Hashed session identifier';
    $userId = 'Hashed user identifier';
    $requestMetadata = new RequestMetadata();
    $requestMetadata->setDomain($domain);
    $requestMetadata->setSessionId($sessionId);
    $requestMetadata->setUserId($userId);
    $histogramQueriesElement = new HistogramQuery();
    $histogramQueriesElement->setHistogramQuery($query);
    $histogramQueries = [$histogramQueriesElement];

    try {
        // Iterate through all elements
        $pagedResponse = $jobServiceClient->searchJobs($formattedParent, $requestMetadata, ['customRankingInfo' => $customRankingInfo, 'orderBy' => $orderBy]);
        foreach ($pagedResponse->iterateAllElements() as $responseItem) {
            printf('Job summary: %s'.PHP_EOL, $responseItem->getJobSummary());
            printf('Job title snippet: %s'.PHP_EOL, $responseItem->getJobTitleSnippet());
            $job = $responseItem->getJob();
            printf('Job name: %s'.PHP_EOL, $job->getName());
            printf('Job title: %s'.PHP_EOL, $job->getTitle());
        }
    } finally {
        $jobServiceClient->close();
    }

    // [END job_search_histogram_search_core]
}
// [END job_search_histogram_search]

$opts = [
    'project_id::',
    'tenant_id::',
    'query::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
    'tenant_id' => 'Your Tenant ID (using tenancy is optional)',
    'query' => 'count(base_compensation, [bucket(12, 20)])',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$tenantId = $options['tenant_id'];
$query = $options['query'];

sampleSearchJobs($projectId, $tenantId, $query);
