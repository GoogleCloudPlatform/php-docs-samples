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


use Google_Service_JobService_CustomAttributeHistogramRequest;
use Google_Service_JobService_HistogramFacets;
use Google_Service_JobService_JobQuery;
use Google_Service_JobService_RequestMetadata;
use Google_Service_JobService_SearchJobsRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The sample in this file introduce how to do a histogram search.
 */
final class HistogramSample extends Command
{
    # [START histogram_search]
    public static function histogram_search(string $companyName = null)
    {
        // Make sure to set the requestMetadata the same as the associated search request
        $requestMetadata = new Google_Service_JobService_RequestMetadata();
        // Make sure to hash your userID
        $requestMetadata->setUserId('HashedUserId');
        // Make sure to hash the sessionID
        $requestMetadata->setSessionId('HashedSessionId');
        // Domain of the website where the search is conducted
        $requestMetadata->setDomain('www.google.com');

        // Constructs HistogramFacets.
        $histogramFacets = new Google_Service_JobService_HistogramFacets();
        $histogramFacets->setSimpleHistogramFacets(array('COMPANY_ID'));
        $customAttributeHistogramRequest = new Google_Service_JobService_CustomAttributeHistogramRequest();
        $customAttributeHistogramRequest->setKey('someFieldName1');
        $customAttributeHistogramRequest->setStringValueHistogram(true);
        $histogramFacets->setCustomAttributeHistogramFacets($customAttributeHistogramRequest);

        // Send search request.
        $request = new Google_Service_JobService_SearchJobsRequest();
        $request->setRequestMetadata($requestMetadata);
        $request->setMode('JOB_SEARCH');
        $request->setHistogramFacets($histogramFacets);
        if (isset($companyName)) {
            $jobQuery = new Google_Service_JobService_JobQuery();
            $jobQuery->setCompanyNames(array($companyName));
            $request->setQuery($jobQuery);
        }

        $response = JobServiceConnector::get_job_service()->jobs->search($request);
        var_export($response);
        return $response;
    }

    # [END histogram_search]

    protected function configure()
    {
        $this
            ->setName('histogram')
            ->setDescription('Run histogram sample script.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $companyToBeCreated = BasicCompanySample::generate_company();
        $companyName = BasicCompanySample::create_company($companyToBeCreated)->getName();

        $jobToBeCreated = CustomAttributeSample::generate_job_with_a_custom_attribute($companyName);
        $jobName = BasicJobSample::create_job($jobToBeCreated)->getName();

        // Wait several seconds for post processing
        sleep(10);
        self::histogram_search($companyName);

        BasicJobSample::delete_job($jobName);
        BasicCompanySample::delete_company($companyName);
    }
}