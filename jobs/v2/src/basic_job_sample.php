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
use Google_Service_JobService_CreateJobRequest;
use Google_Service_JobService_Job;
use Google_Service_JobService_UpdateJobRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This file contains the basic knowledge about job, including:
 *
 * - Construct a job with required fields
 *
 * - Create a job
 *
 * - Get a job
 *
 * - Update a job
 *
 * - Update a job with field mask
 *
 * - Delete a job
 */
final class BasicJobSample extends Command
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

    # [START basic_job]

    /**
     * Generates a basic job with given companyName.
     *
     * @param string $companyName
     * @return Google_Service_JobService_Job
     */
    public static function generate_job_with_required_fields($companyName)
    {
        $requisitionId = 'jobWithRequiredFields:' . rand();

        $job = new Google_Service_JobService_Job();
        $job->setRequisitionId($requisitionId);
        $job->setJobTitle('Software Engineer');
        $job->setCompanyName($companyName);
        $job->setApplicationUrls(array('http://careers.google.com'));
        $job->setDescription('Design, develop, test, deploy, maintain and improve software.');
        printf("Job generated:\n%s\n", var_export($job, true));
        return $job;
    }

    # [END basic_job]

    # [START create_job]
    /**
     * Creates a job.
     *
     * @param Google_Service_JobService_Job $jobToBeCreated
     * @return Google_Service_JobService_Job
     */
    public static function create_job(Google_Service_JobService_Job $jobToBeCreated)
    {
        $jobService = self::get_job_service();

        $createJobRequest = new Google_Service_JobService_CreateJobRequest();
        $createJobRequest->setJob($jobToBeCreated);
        $jobCreated = $jobService->jobs->create($createJobRequest);
        printf("Job created:\n%s\n", var_export($jobCreated, true));
        return $jobCreated;
    }
    # [END create_job]

    # [START get_job]
    /**
     * Gets a job by jobName.
     *
     * @param string $jobName
     * @return Google_Service_JobService_Job
     */
    public static function get_job($jobName)
    {
        $jobService = self::get_job_service();

        $jobExisted = $jobService->jobs->get($jobName);
        printf("Job existed:\n%s\n", var_export($jobExisted, true));
        return $jobExisted;
    }
    # [END get_job]

    # [START update_job]
    /**
     * Updates a job.
     *
     * @param string $jobName
     * @param Google_Service_JobService_Job $jobToBeUpdated
     * @return Google_Service_JobService_Job
     */
    public static function update_job($jobName, Google_Service_JobService_Job $jobToBeUpdated)
    {
        $jobService = self::get_job_service();

        $updateJobRequest = new Google_Service_JobService_UpdateJobRequest();
        $updateJobRequest->setJob($jobToBeUpdated);
        $jobUpdated = $jobService->jobs->patch($jobName, $updateJobRequest);
        printf("Job updated:\n%s\n", var_export($jobUpdated, true));
        return $jobUpdated;
    }
    # [END update_job]

    # [START update_job_with_field_mask]
    /**
     * Updates a job with field mask.
     *
     * @param string $jobName
     * @param string $fieldMask
     * @param Google_Service_JobService_Job $jobToBeUpdated
     * @return Google_Service_JobService_Job
     */
    public static function update_job_with_field_mask(
        $jobName,
        $fieldMask,
        Google_Service_JobService_Job $jobToBeUpdated
    ) {
        $jobService = self::get_job_service();

        $updateJobRequest = new Google_Service_JobService_UpdateJobRequest();
        $updateJobRequest->setJob($jobToBeUpdated);
        $updateJobRequest->setUpdateJobFields($fieldMask);

        $jobUpdated = $jobService->jobs->patch($jobName, $updateJobRequest);
        printf("Job updated:\n%s\n", var_export($jobUpdated, true));
        return $jobUpdated;
    }
    # [END update_job_with_field_mask]

    # [START delete_job]
    public static function delete_job($jobName)
    {
        $jobService = self::get_job_service();

        $jobService->jobs->delete($jobName);
        echo 'Job deleted' . PHP_EOL;
    }

    # [END delete_job]


    protected function configure()
    {
        $this
            ->setName('basic-job')
            ->setDescription('Run basic job sample script to create, update, and delete a job.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Create a company before creating jobs.
        $companyToBeCreated = BasicCompanySample::generate_company();
        $companyCreated = BasicCompanySample::create_company($companyToBeCreated);
        $companyName = $companyCreated->getName();

        // Construct a job.
        $jobToBeCreated = self::generate_job_with_required_fields($companyName);

        // Create a job.
        $jobCreated = self::create_job($jobToBeCreated);

        // Get a job.
        $jobName = $jobCreated->getName();
        self::get_job($jobName);

        // Update a job.
        $jobToBeUpdated = clone $jobCreated;
        $jobToBeUpdated->setDescription('changedDescription');
        self::update_job($jobName, $jobToBeUpdated);

        // Update a job with field mask.
        $jobToBeUpdated = new Google_Service_JobService_Job();
        $jobToBeUpdated->setJobTitle('changedJobTitle');
        self::update_job_with_field_mask($jobName, 'jobTitle', $jobToBeUpdated);

        // Delete a job.
        self::delete_job($jobName);

        // Delete company only after cleaning all jobs under this company.
        BasicCompanySample::delete_company($companyName);
    }
}
