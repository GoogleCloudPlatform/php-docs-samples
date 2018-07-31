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
use Google_Service_CloudTalentSolution;
use Google_Service_CloudTalentSolution_CompensationEntry;
use Google_Service_CloudTalentSolution_CompensationInfo;
use Google_Service_CloudTalentSolution_JobQuery;
use Google_Service_CloudTalentSolution_Money;
use Google_Service_CloudTalentSolution_RequestMetadata;
use Google_Service_CloudTalentSolution_SearchJobsRequest;
use Google_Service_CloudTalentSolution_SearchJobsResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The samples in this file introduce how to do a general search, including:
 *
 * - Basic keyword search
 *
 * - Filter on categories
 *
 * - Filter on employment types
 *
 * - Filter on date range
 *
 * - Filter on language codes
 *
 * - Filter on company display names
 *
 * - Filter on compensations
 */
final class GeneralSearchSample extends Command
{
    private static $jobService;

    /**
     * Creates a Google_Service_CloudTalentSolution with default application credentials.
     * @return Google_Service_CloudTalentSolution
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
        $jobService = new Google_Service_CloudTalentSolution($client);
        return $jobService;
    }

    /**
     * Gets Google_Service_CloudTalentSolution.
     *
     * @return Google_Service_CloudTalentSolution
     */
    private static function get_job_service()
    {
        if (!isset(self::$jobService)) {
            self::$jobService = self::create_job_service();
        }
        return self::$jobService;
    }
    # [START basic_keyword_search]

    /**
     * Simple job search with keyword.
     *
     * @param string $parent
     * @param string|null $companyName
     * @param string $query
     * @return Google_Service_CloudTalentSolution_SearchJobsResponse
     */
    public static function basic_search_jobs($parent, $companyName = null, $query)
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_CloudTalentSolution_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        // Perform a search for analyst related jobs
        $jobQuery = new Google_Service_CloudTalentSolution_JobQuery();
        $jobQuery->setQuery($query);
        if (isset($companyName)) {
            $jobQuery->setCompanyNames(array($companyName));
        }

        $searchRequest = new Google_Service_CloudTalentSolution_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setJobQuery($jobQuery);
        $searchRequest->setSearchMode('JOB_SEARCH');

        $jobService = self::get_job_service();
        $response = $jobService->projects_jobs->search($parent, $searchRequest);

        var_export($response);
        return $response;
    }
    # [END basic_keyword_search]

    protected function configure()
    {
        $this
            ->setName('general-search')
            ->setDescription('Run general search sample script to do search with different filters.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectId = getenv('GOOGLE_PROJECT_ID');
        $parent = sprintf('projects/%s', $projectId);

        $companyToBeCreated = BasicCompanySample::generate_company();
        $companyToBeCreated->setDisplayName('Google');
        $companyName = BasicCompanySample::create_company($parent, $companyToBeCreated)->getName();

        $jobToBeCreated = BasicJobSample::generate_job_with_required_fields($companyName);
        $jobToBeCreated->setTitle('Systems Administrator');
        $jobToBeCreated->setEmploymentTypes(array('FULL_TIME'));
        $jobToBeCreated->setLanguageCode('en-US');
        $compensationEntry = new Google_Service_CloudTalentSolution_CompensationEntry();
        $compensationEntry->setType('BASE');
        $compensationEntry->setUnit('HOURLY');
        $amount = new Google_Service_CloudTalentSolution_Money();
        $amount->setCurrencyCode('USD');
        $amount->setUnits(12);
        $compensationEntry->setAmount($amount);
        $compensationInfo = new Google_Service_CloudTalentSolution_CompensationInfo();
        $compensationInfo->setEntries(array($compensationEntry));
        $jobToBeCreated->setCompensationInfo($compensationInfo);

        $jobName = BasicJobSample::create_job($parent, $jobToBeCreated)->getName();

        // Wait several seconds for post processing.
        sleep(10);
        self::basic_search_jobs($parent, $companyName, 'Systems Administrator');

        BasicJobSample::delete_job($jobName);
        BasicCompanySample::delete_company($companyName);
    }
}
