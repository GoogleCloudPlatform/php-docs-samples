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
use Google_Service_JobService_CustomAttribute;
use Google_Service_JobService_Job;
use Google_Service_JobService_JobQuery;
use Google_Service_JobService_RequestMetadata;
use Google_Service_JobService_SearchJobsRequest;
use Google_Service_JobService_SearchJobsResponse;
use Google_Service_JobService_StringValues;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This file contains the samples about CustomAttribute, including:
 *
 * - Construct a Job with CustomAttribute
 *
 * - Search Job with CustomAttributeFilter
 */
final class CustomAttributeSample extends Command
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

    # [START custom_attribute_job]

    /**
     * Generates a job with a custom attribute.
     *
     * @param string $companyName
     * @return Google_Service_JobService_Job
     */
    public static function generate_job_with_a_custom_attribute($companyName)
    {
        $requisitionId = 'jobWithACustomAttribute:' . rand();

        // Constructs custom attributes array.
        $customAttribute1 = new Google_Service_JobService_CustomAttribute();
        $stringValues = new Google_Service_JobService_StringValues();
        $stringValues->setValues(array('value1'));
        $customAttribute1->setStringValues($stringValues);
        $customAttribute1->setFilterable(true);
        $customAttribute2 = new Google_Service_JobService_CustomAttribute();
        $customAttribute2->setLongValue(256);
        $customAttribute2->setFilterable(true);
        $customAttributes = array('someFieldName1' => $customAttribute1, 'someFieldName2' => $customAttribute2);

        // Creates job with custom attributes.
        $job = new Google_Service_JobService_Job();
        $job->setCompanyName($companyName);
        $job->setRequisitionId($requisitionId);
        $job->setJobTitle('Software Engineer');
        $job->setApplicationUrls(array('https://careers.google.com'));
        $job->setDescription('Design, develop, test, deploy, maintain and improve software.');
        $job->setCustomAttributes($customAttributes);

        printf("Job generated:\n%s\n", var_export($job, true));
        return $job;
    }
    # [END custom_attribute_job]

    # [START custom_attribute_filter_string_value]
    /**
     * CustomAttributeFilter on String value CustomAttribute
     *
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function filters_on_string_value_custom_attribute()
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        $customAttributeFilter = 'NOT EMPTY(someFieldName1)';
        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setCustomAttributeFilter($customAttributeFilter);

        $searchJobsRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchJobsRequest->setQuery($jobQuery);
        $searchJobsRequest->setRequestMetadata($requestMetadata);
        $searchJobsRequest->setJobView('FULL');

        $response = self::get_job_service()->jobs->search($searchJobsRequest);
        var_export($response);
        return $response;
    }
    # [END custom_attribute_filter_string_value]

    # [START custom_attribute_filter_long_value]
    /**
     * CustomAttributeFilter on Long value CustomAttribute
     *
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function filters_on_long_value_custom_attribute()
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        $customAttributeFilter = '(255 <= someFieldName2) AND (someFieldName2 <= 257)';
        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setCustomAttributeFilter($customAttributeFilter);

        $searchJobsRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchJobsRequest->setQuery($jobQuery);
        $searchJobsRequest->setRequestMetadata($requestMetadata);
        $searchJobsRequest->setJobView('FULL');

        $response = self::get_job_service()->jobs->search($searchJobsRequest);
        var_export($response);
        return $response;
    }
    # [END custom_attribute_filter_long_value]

    # [START custom_attribute_filter_multi_attributes]
    /**
     * CustomAttributeFilter on multiple CustomAttributes
     *
     * @return Google_Service_JobService_SearchJobsResponse
     */
    public static function filters_on_multi_custom_attribute()
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        $customAttributeFilter = '(someFieldName1 = "value1") AND ((255 <= someFieldName2) OR (someFieldName2 <= 213))';
        $jobQuery = new Google_Service_JobService_JobQuery();
        $jobQuery->setCustomAttributeFilter($customAttributeFilter);

        $searchJobsRequest = new Google_Service_JobService_SearchJobsRequest();
        $searchJobsRequest->setQuery($jobQuery);
        $searchJobsRequest->setRequestMetadata($requestMetadata);
        $searchJobsRequest->setJobView('FULL');

        $response = self::get_job_service()->jobs->search($searchJobsRequest);
        var_export($response);
        return $response;
    }

    # [END custom_attribute_filter_multi_attributes]

    protected function configure()
    {
        $this
            ->setName('custom-attribute')
            ->setDescription('Run custom attribute sample script to search on custom attributes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $companyToBeCreated = BasicCompanySample::generate_company();
        $companyName = BasicCompanySample::create_company($companyToBeCreated)->getName();

        $jobToBeCreated = self::generate_job_with_a_custom_attribute($companyName);
        $jobName = BasicJobSample::create_job($jobToBeCreated)->getName();

        // Wait several seconds for post processing
        sleep(10);
        self::filters_on_string_value_custom_attribute();
        self::filters_on_long_value_custom_attribute();
        self::filters_on_multi_custom_attribute();

        BasicJobSample::delete_job($jobName);
        BasicCompanySample::delete_company($companyName);
    }
}
