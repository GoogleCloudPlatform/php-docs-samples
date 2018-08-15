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
use Google_Service_JobService_CommutePreference;
use Google_Service_JobService_JobQuery;
use Google_Service_JobService_LatLng;
use Google_Service_JobService_RequestMetadata;
use Google_Service_JobService_SearchJobsRequest;
use Google_Service_JobService_SearchJobsResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The samples in this file introduce how to do a commute search.
 *
 * Note: Commute Search is different from location search. It only take latitude and longitude as
 * the start location.
 */
final class CommuteSearchSample extends Command
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

    # [START commute_search]

    /**
     * Search jobs based on commute location and time.
     *
     * @param string|null $companyName
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function commute_search($companyName = null)
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        // Create commute search filter.
        $commuteFilter = new Google_Service_JobService_CommutePreference();
        $commuteFilter->setRoadTraffic('TRAFFIC_FREE');
        $commuteFilter->setMethod('TRANSIT');
        $commuteFilter->setTravelTime('1000s');
        $startLocation = new Google_Service_JobService_LatLng();
        $startLocation->setLatitude(37.422408);
        $startLocation->setLongitude(-122.085609);
        $commuteFilter->setStartLocation($startLocation);

        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setCommuteFilter($commuteFilter);
        if (isset($companyName)) {
            $jobQuery->setCompanyNames(array($companyName));
        }

        $searchRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchRequest->setRequestMetadata($requestMetadata);
        $searchRequest->setQuery($jobQuery);
        $searchRequest->setJobView('FULL');
        $searchRequest->setEnablePreciseResultSize(true);

        $jobService = self::get_job_service();
        $response = $jobService->jobs->search($searchRequest);

        var_export($response);
        return $response;
    }

    # [END commute_search]

    protected function configure()
    {
        $this
            ->setName('commute-search')
            ->setDescription('Run commute search sample script to search based on commute location and time.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Create a company first.
        $companyName = BasicCompanySample::create_company(BasicCompanySample::generate_company())->getName();

        // Create a job with location.
        $jobToBeCreated = BasicJobSample::generate_job_with_required_fields($companyName);
        $jobToBeCreated->setLocations(array('1600 Amphitheatre Pkwy, Mountain View, CA 94043'));
        $jobName = BasicJobSample::create_job($jobToBeCreated)->getName();

        // Wait several seconds for post processing.
        sleep(10);
        self::commute_search($companyName);

        BasicJobSample::delete_job($jobName);
        BasicCompanySample::delete_company($companyName);
    }
}
