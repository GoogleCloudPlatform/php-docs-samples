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
use Google_Service_JobService_CompensationEntry;
use Google_Service_JobService_CompensationFilter;
use Google_Service_JobService_CompensationInfo;
use Google_Service_JobService_CompensationRange;
use Google_Service_JobService_JobQuery;
use Google_Service_JobService_Money;
use Google_Service_JobService_RequestMetadata;
use Google_Service_JobService_SearchJobsRequest;
use Google_Service_JobService_SearchJobsResponse;
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
    # [START basic_keyword_search]

    /**
     * Simple job search with keyword.
     *
     * @param string|null $companyName
     * @param string $query
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function basic_search_jobs($companyName = null, $query)
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        // Perform a search for analyst related jobs
        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setQuery($query);
        if (isset($companyName)) {
            $jobQuery->setCompanyNames(array($companyName));
        }

        $searchRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setQuery($jobQuery);
        $searchRequest->setMode('JOB_SEARCH');

        $jobService = self::get_job_service();
        $response = $jobService->jobs->search($searchRequest);

        var_export($response);
        return $response;
    }
    # [END basic_keyword_search]

    # [START category_filter]
    /**
     * Search on category filter.
     *
     * @param string|null $companyName
     * @param string[] $categories
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function category_filter_search($companyName = null, array $categories)
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
        $jobQuery->setCategories($categories);
        if (isset($companyName)) {
            $jobQuery->setCompanyNames(array($companyName));
        }

        $searchRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setQuery($jobQuery);
        $searchRequest->setMode('JOB_SEARCH');

        $jobService = self::get_job_service();
        $response = $jobService->jobs->search($searchRequest);

        var_export($response);
        return $response;
    }

    # [START employment_types_filter]

    /**
     * Search on employment types.
     *
     * @param string|null $companyName
     * @param string[] $employmentTypes
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function employment_types_search($companyName = null, array $employmentTypes)
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
        $jobQuery->setEmploymentTypes($employmentTypes);
        if (isset($companyName)) {
            $jobQuery->setCompanyNames(array($companyName));
        }

        $searchRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setQuery($jobQuery);
        $searchRequest->setMode('JOB_SEARCH');

        $jobService = self::get_job_service();
        $response = $jobService->jobs->search($searchRequest);

        var_export($response);
        return $response;
    }
    # [END employment_types_filter]

    # [START date_range_filter]
    /**
     * Search by date range.
     *
     * @param string|null $companyName
     * @param string $dateRange
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function date_range_search($companyName = null, $dateRange)
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
        $jobQuery->setPublishDateRange($dateRange);
        if (isset($companyName)) {
            $jobQuery->setCompanyNames(array($companyName));
        }

        $searchRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setQuery($jobQuery);
        $searchRequest->setMode('JOB_SEARCH');

        $jobService = self::get_job_service();
        $response = $jobService->jobs->search($searchRequest);

        var_export($response);
        return $response;
    }
    # [END date_range_filter]

    # [START language_code_filter]
    /**
     * Search by language code.
     *
     * @param string|null $companyName
     * @param string[] $languageCodes
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function language_code_search($companyName = null, array $languageCodes)
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
        $jobQuery->setLanguageCodes($languageCodes);
        if (isset($companyName)) {
            $jobQuery->setCompanyNames(array($companyName));
        }

        $searchRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setQuery($jobQuery);
        $searchRequest->setMode('JOB_SEARCH');

        $jobService = self::get_job_service();
        $response = $jobService->jobs->search($searchRequest);

        var_export($response);
        return $response;
    }
    # [END language_code_filter]

    # [START company_display_name_filter]
    /**
     * Search on company display name.
     *
     * @param string|null $companyName
     * @param string[] $companyDisplayNames
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function company_display_name_search($companyName = null, array $companyDisplayNames)
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
        $jobQuery->setCompanyDisplayNames($companyDisplayNames);
        if (!empty($companyName)) {
            $jobQuery->setCompanyNames($companyName);
        }

        $searchRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setQuery($jobQuery);
        $searchRequest->setMode('JOB_SEARCH');

        $jobService = self::get_job_service();
        $response = $jobService->jobs->search($searchRequest);

        var_export($response);
        return $response;
    }
    # [END company_display_name_filter]

    # [START compensation_filter]
    /**
     * Search on compensation.
     *
     * @param string|null $companyName
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function compensation_search($companyName = null)
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        $compensationFilter = new Google_Service_JobService_CompensationFilter();
        $compensationFilter->setType('UNIT_AND_AMOUNT');
        $compensationFilter->setUnits(array('HOURLY'));

        $compensationRange = new Google_Service_JobService_CompensationRange();
        $maxMoney = new Google_Service_JobService_Money();
        $maxMoney->setCurrencyCode('USD');
        $maxMoney->setUnits(15);
        $compensationRange->setMax($maxMoney);
        $minMoney = new Google_Service_JobService_Money();
        $minMoney->setCurrencyCode('USD');
        $minMoney->setUnits(10);
        $minMoney->setNanos(500000000);
        $compensationRange->setMin($minMoney);
        $compensationFilter->setRange($compensationRange);

        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setCompensationFilter($compensationFilter);
        if (isset($companyName)) {
            $jobQuery->setCompanyNames(array($companyName));
        }

        $searchRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setQuery($jobQuery);
        $searchRequest->setMode('JOB_SEARCH');

        $jobService = self::get_job_service();
        $response = $jobService->jobs->search($searchRequest);

        var_export($response);
        return $response;
    }

    # [END compensation_filter]

    protected function configure()
    {
        $this
            ->setName('general-search')
            ->setDescription('Run general search sample script to do search with different filters.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $companyToBeCreated = BasicCompanySample::generate_company();
        $companyToBeCreated->setDisplayName('Google');
        $companyName = BasicCompanySample::create_company($companyToBeCreated)->getName();

        $jobToBeCreated = BasicJobSample::generate_job_with_required_fields($companyName);
        $jobToBeCreated->setJobTitle('Systems Administrator');
        $jobToBeCreated->setEmploymentTypes(array('FULL_TIME'));
        $jobToBeCreated->setLanguageCode('en-US');
        $compensationEntry = new Google_Service_JobService_CompensationEntry();
        $compensationEntry->setType('BASE');
        $compensationEntry->setUnit('HOURLY');
        $amount = new Google_Service_JobService_Money();
        $amount->setCurrencyCode('USD');
        $amount->setUnits(12);
        $compensationEntry->setAmount($amount);
        $compensationInfo = new Google_Service_JobService_CompensationInfo();
        $compensationInfo->setEntries(array($compensationEntry));
        $jobToBeCreated->setCompensationInfo($compensationInfo);

        $jobName = BasicJobSample::create_job($jobToBeCreated)->getName();

        // Wait several seconds for post processing.
        sleep(10);
        self::basic_search_jobs($companyName, 'Systems Administrator');
        self::category_filter_search($companyName, ['COMPUTER_AND_IT']);
        self::date_range_search($companyName, 'PAST_24_HOURS');
        self::employment_types_search($companyName, ['FULL_TIME', 'CONTRACTOR', 'PER_DIEM']);
        self::company_display_name_search($companyName, ['Google']);
        self::compensation_search($companyName);
        self::language_code_search($companyName, ['pt-BR', 'en-US']);

        BasicJobSample::delete_job($jobName);
        BasicCompanySample::delete_company($companyName);
    }
}
