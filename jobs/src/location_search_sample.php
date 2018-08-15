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
use Google_Service_JobService_JobQuery;
use Google_Service_JobService_LocationFilter;
use Google_Service_JobService_RequestMetadata;
use Google_Service_JobService_SearchJobsRequest;
use Google_Service_JobService_SearchJobsResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The samples in this file introduce how to do a search with location filter, including:
 *
 * - Basic search with location filter
 *
 * - Keyword search with location filter
 *
 * - Location filter on city level
 *
 * - Broadening search with location filter
 *
 * - Location filter of multiple locations
 */
final class LocationSearchSample extends Command
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

    # [START basic_location_search]

    /**
     * Basic location search.
     *
     * @param string|null $companyName
     * @param string $location
     * @param float $distance
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function basic_location_search($companyName = null, $location, $distance)
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        $locationFilter = new Google_Service_JobService_LocationFilter();
        $locationFilter->setName($location);
        $locationFilter->setDistanceInMiles($distance);
        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setLocationFilters(array($locationFilter));
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
    # [END basic_location_search]

    # [START keyword_location_search]
    /**
     * Keyword location search.
     *
     * @param string|null $companyName
     * @param string $location
     * @param float $distance
     * @param string $keyword
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function keyword_location_search(
        $companyName = null,
        $location,
        $distance,
        $keyword
    ) {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        $locationFilter = new Google_Service_JobService_LocationFilter();
        $locationFilter->setName($location);
        $locationFilter->setDistanceInMiles($distance);

        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setQuery($keyword);
        $jobQuery->setLocationFilters(array($locationFilter));
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
    # [END keyword_location_search]

    # [START city_location_search]
    /**
     * City location search.
     *
     * @param string|null $companyName
     * @param string $location
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function city_location_search($companyName = null, $location)
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        $locationFilter = new Google_Service_JobService_LocationFilter();
        $locationFilter->setName($location);

        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setLocationFilters(array($locationFilter));
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
    # [END city_location_search]

    # [START multi_locations_search]
    /**
     * Multiple locations search.
     *
     * @param string|null $companyName
     * @param string $location1
     * @param float $distance1
     * @param string $location2
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function multi_locations_search(
        $companyName = null,
        $location1,
        $distance1,
        $location2
    ) {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        $locationFilter1 = new Google_Service_JobService_LocationFilter();
        $locationFilter1->setName($location1);
        $locationFilter1->setDistanceInMiles($distance1);

        $locationFilter2 = new Google_Service_JobService_LocationFilter();
        $locationFilter2->setName($location2);

        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setLocationFilters(array($locationFilter1, $locationFilter2));
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
    # [END multi_locations_search]

    # [START broadening_location_search]
    /**
     * Broadening location search.
     *
     * @param string|null $companyName
     * @param string $location
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function broadening_location_search($companyName = null, $location)
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        $locationFilter = new Google_Service_JobService_LocationFilter();
        $locationFilter->setName($location);

        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setLocationFilters(array($locationFilter));
        if (isset($companyName)) {
            $jobQuery->setCompanyNames(array($companyName));
        }

        $searchRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setQuery($jobQuery);
        // Enable broadening.
        $searchRequest->setEnableBroadening(true);
        $searchRequest->setMode('JOB_SEARCH');

        $jobService = self::get_job_service();
        $response = $jobService->jobs->search($searchRequest);

        var_export($response);
        return $response;
    }

    # [END broadening_location_search]

    protected function configure()
    {
        $this
            ->setName('location-search')
            ->setDescription('Run location search sample script to do location search.')
            ->addOption('location', null, InputOption::VALUE_OPTIONAL, 'The location to search.', 'Mountain View, CA')
            ->addOption('distance', null, InputOption::VALUE_OPTIONAL, 'Distance in miles to search.', 0.5)
            ->addOption(
                'keyword',
                null,
                InputOption::VALUE_OPTIONAL,
                'The keyword used in keyword search sample',
                'Software Engineer'
            )
            ->addOption(
                'location2',
                null,
                InputOption::VALUE_OPTIONAL,
                'Second location in multiple locations search sample',
                'Sunnyvale, CA'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $location = $input->getOption('location');
        $distance = $input->getOption('distance');
        $keyword = $input->getOption('keyword');
        $location2 = $input->getOption('location2');

        // Create a company.
        $companyToBeCreated = BasicCompanySample::generate_company();
        $companyName = BasicCompanySample::create_company($companyToBeCreated)->getName();

        // Create first job.
        $jobToBeCreated = BasicJobSample::generate_job_with_required_fields($companyName);
        $jobToBeCreated->setLocations(array($location));
        $jobToBeCreated->setJobTitle($keyword);
        $jobName = BasicJobSample::create_job($jobToBeCreated)->getName();

        // Create second job.
        $jobToBeCreated2 = BasicJobSample::generate_job_with_required_fields($companyName);
        $jobToBeCreated2->setLocations(array($location2));
        $jobToBeCreated2->setJobTitle($keyword);
        $jobName2 = BasicJobSample::create_job($jobToBeCreated2)->getName();

        // Wait several seconds for post processing.
        sleep(10);
        self::basic_location_search($companyName, $location, $distance);
        self::city_location_search($companyName, $location);
        self::broadening_location_search($companyName, $location);
        self::keyword_location_search($companyName, $location, $distance, $keyword);
        self::multi_locations_search($companyName, $location, $distance, $location2);

        // Delete jobs before deleting the company.
        BasicJobSample::delete_job($jobName);
        BasicJobSample::delete_job($jobName2);
        BasicCompanySample::delete_company($companyName);
    }
}
