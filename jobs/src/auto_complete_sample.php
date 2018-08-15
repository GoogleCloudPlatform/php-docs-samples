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
use Google_Service_JobService_CompleteQueryResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The samples in this file introduced how to do the auto complete, including:
 *
 * - Default auto complete (on both company display name and job title)
 *
 * - Auto complete on job title only
 */
final class AutoCompleteSample extends Command
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

    # [START auto_complete_job_title]

    /**
     * Auto completes job titles within given companyName.
     *
     * @param string|null $companyName
     * @param string $query
     * @return Google_Service_JobService_CompleteQueryResponse
     */
    public static function job_title_auto_complete($companyName = null, $query)
    {
        $optParams = array(
            'query' => $query,
            'languageCode' => 'en-US',
            'type' => 'JOB_TITLE',
            'pageSize' => 10);
        if (isset($companyName)) {
            $optParams['companyName'] = $companyName;
        }

        $jobService = self::get_job_service();
        $results = $jobService->v2->complete($optParams);

        var_export($results);
        return $results;
    }

    # [END auto_complete_job_title]

    # [START auto_complete_default]
    /**
     * Auto completes job titles within given companyName.
     *
     * @param string|null $companyName
     * @param string $query
     * @return Google_Service_JobService_CompleteQueryResponse
     */
    public static function default_auto_complete($companyName = null, $query)
    {
        $optParams = array(
            'query' => $query,
            'languageCode' => 'en-US',
            'pageSize' => 10);
        if (isset($companyName)) {
            $optParams['companyName'] = $companyName;
        }

        $jobService = self::get_job_service();
        $results = $jobService->v2->complete($optParams);

        var_export($results);
        return $results;
    }

    # [END auto_complete_default]

    protected function configure()
    {
        $this
            ->setName('auto-complete')
            ->setDescription('Run auto complete sample script.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $companyToBeCreated = BasicCompanySample::generate_company();
        $companyToBeCreated->setDisplayName('Google');
        $companyName = BasicCompanySample::create_company($companyToBeCreated)->getName();

        $jobToBeCreated = BasicJobSample::generate_job_with_required_fields($companyName);
        $jobToBeCreated->setJobTitle('Software engineer');
        $jobName = BasicJobSample::create_job($jobToBeCreated)->getName();

        // Wait several seconds for post processing.
        sleep(10);
        self::default_auto_complete($companyName, 'goo');
        self::default_auto_complete($companyName, 'sof');
        self::job_title_auto_complete($companyName, 'sof');

        BasicJobSample::delete_job($jobName);
        BasicCompanySample::delete_company($companyName);
    }
}
