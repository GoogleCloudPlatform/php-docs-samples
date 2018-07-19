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

namespace Google\Cloud\Samples\Jobs;

use Google_Client;
use Google_Service_JobService;
use Google_Service_JobService_Job;
use Google_Service_JobService_JobQuery;
use Google_Service_JobService_RequestMetadata;
use Google_Service_JobService_SearchJobsRequest;
use Google_Service_JobService_SearchJobsResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The sample in this file introduces featured job, including:
 *
 * - Construct a featured job
 *
 * - Search featured job
 */
final class FeaturedJobsSearchSample extends Command
{
    private static $jobService;

    /**
     * Creates a Google_Service_JobService with default application credentials.
     * @return Google_Service_JobService
     */
    private static function create_job_service()
    {
        // Instantiate the client
        $client = new Google_Client();

        // Authorize the client using Application Default Credentials
        // @see https://developers.google.com/identity/protocols/application-default-credentials
        $client->useApplicationDefaultCredentials();
        $client->setScopes(array('https://www.googleapis.com/auth/jobs'));

        // Instantiate the Cloud Job Discovery Service API
        $jobService = new Google_Service_JobService($client);
        return $jobService;
    }

    /**
     * Gets Google_Service_JobService.
     *
     * @return Google_Service_JobService
     */
    private static function get_job_service()
    {
        if (!isset(self::$jobService)) {
            self::$jobService = self::create_job_service();
        }
        return self::$jobService;
    }

    # [START featured_job]

    /**
     * Creates a job ad featured.
     *
     * @param string $companyName
     * @return Google_Service_JobService_Job
     */
    public static function generate_featured_job($companyName)
    {
        $requisitionId = 'featuredJob:' . rand();

        $job = new Google_Service_JobService_Job();
        $job->setRequisitionId($requisitionId);
        $job->setJobTitle('Software Engineer');
        $job->setCompanyName($companyName);
        $job->setApplicationUrls(array('https://careers.google.com'));
        $job->setDescription('Design, develop, test, deploy, maintain and improve software.');
        // Featured job is the job with positive promotion value
        $job->setPromotionValue(2);

        printf("Job generated:\n%s\n", var_export($job, true));
        return $job;
    }
    # [END featured_job]

    # [START search_featured_job]
    /**
     * Searches featured jobs.
     *
     * @param string|null $companyName
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function search_featured_jobs($companyName = null)
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setQuery('Software Engineer');
        if (isset($companyName)) {
            $jobQuery->setCompanyNames(array($companyName));
        }

        $searchRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setQuery($jobQuery);
        // Set the search mode to a featured search,
        // which would only search the jobs with positive promotion value.
        $searchRequest->setMode('FEATURED_JOB_SEARCH');

        $jobService = self::get_job_service();
        $response = $jobService->jobs->search($searchRequest);

        var_export($response);
        return $response;
    }

    # [END search_featured_job]

    protected function configure()
    {
        $this
            ->setName('featured-jobs-search')
            ->setDescription('Run featured jobs search sample script.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $companyToBeCreated = BasicCompanySample::generate_company();
        $companyName = BasicCompanySample::create_company($companyToBeCreated)->getName();

        $jobToBeCreated = self::generate_featured_job($companyName);
        $jobName = BasicJobSample::create_job($jobToBeCreated)->getName();

        // Wait several seconds for post processing
        sleep(10);
        self::search_featured_jobs($companyName);

        BasicJobSample::delete_job($jobName);
        BasicCompanySample::delete_company($companyName);
    }
}
