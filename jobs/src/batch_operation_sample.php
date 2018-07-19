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
use Google_Service_Exception;
use Google_Service_JobService;
use Google_Service_JobService_CreateJobRequest;
use Google_Service_JobService_Job;
use Google_Service_JobService_UpdateJobRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The samples in this file introduce how to do batch operation in CJD. Including:
 *
 * - Create job within batch
 *
 * - Update job within batch
 *
 * - Delete job within batch.
 *
 * For simplicity, the samples always use the same kind of requests in each batch. In a real case ,
 * you might put different kinds of request in one batch.
 */
final class BatchOperationSample extends Command
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

    # [START batch_job_create]

    /**
     * Creates jobs in batch.
     *
     * @param string $companyName
     * @return array
     */
    public static function batch_create_jobs($companyName)
    {
        $jobService = self::get_job_service();
        $jobService->getClient()->setUseBatch(true);

        $softwareEngineerJob = new Google_Service_JobService_Job();
        $softwareEngineerJob->setCompanyName($companyName);
        $softwareEngineerJob->setRequisitionId('123456');
        $softwareEngineerJob->setJobTitle('Software Engineer');
        $softwareEngineerJob->setApplicationUrls(array('https://careers.google.com'));
        $softwareEngineerJob->setDescription('Design, develop, test, deploy, maintain and improve software.');

        $hardwareEngineerJob = new Google_Service_JobService_Job();
        $hardwareEngineerJob->setCompanyName($companyName);
        $hardwareEngineerJob->setRequisitionId('1234567');
        $hardwareEngineerJob->setJobTitle('Hardware Engineer');
        $hardwareEngineerJob->setApplicationUrls(array('https://careers.google.com'));
        $hardwareEngineerJob->setDescription('Design prototype PCBs or modify existing board designs to prototype new features or functions.');

        // Creates batch request.
        $batchCreate = $jobService->createBatch();

        $createJobRequest1 = new Google_Service_JobService_CreateJobRequest();
        $createJobRequest1->setJob($softwareEngineerJob);
        $batchRequest1 = $jobService->jobs->create($createJobRequest1);
        $batchCreate->add($batchRequest1);

        $createJobRequest2 = new Google_Service_JobService_CreateJobRequest();
        $createJobRequest2->setJob($hardwareEngineerJob);
        $batchRequest2 = $jobService->jobs->create($createJobRequest2);
        $batchCreate->add($batchRequest2);

        $results = $batchCreate->execute();
        // Disable batch mode.
        $jobService->getClient()->setUseBatch(false);
        $createdJobs = array();
        foreach ($results as $result) {
            if ($result instanceof Google_Service_Exception) {
                printf("Create Error Message:\n%s\n", $result->getMessage());
            } else {
                printf("Create Job:\n%s\n", var_export($result, true));
                array_push($createdJobs, $result);
            }
        }

        return $createdJobs;
    }

    # [END batch_job_create]

    # [START batch_job_update]
    /**
     * Updates jobs in batch.
     *
     * @param Google_Service_JobService_Job[] $jobsToBeUpdated
     * @return array
     */
    public static function batch_job_update(array $jobsToBeUpdated)
    {
        $jobService = self::get_job_service();
        $jobService->getClient()->setUseBatch(true);

        // Creates batch request.
        $batchUpdate = $jobService->createBatch();
        $i = 0;
        foreach ($jobsToBeUpdated as $job) {
            if ($i % 2 == 0) {
                // You might use Job entity with all fields filled in to do the update
                $job->setJobTitle('Engineer in Mountain View');
                $updateRequest = new Google_Service_JobService_UpdateJobRequest();
                $updateRequest->setJob($job);
                $batchUpdate->add($jobService->jobs->patch($job->getName(), $updateRequest));
            } else {
                // Or just fill in part of field in Job entity and set the updateJobFields
                $newJob = new Google_Service_JobService_Job();
                $newJob->setJobTitle('Engineer in Mountain View');
                $newJob->setName($job->getName());
                $updateRequest = new Google_Service_JobService_UpdateJobRequest();
                $updateRequest->setJob($newJob);
                $updateRequest->setUpdateJobFields('jobTitle');
                $batchUpdate->add($jobService->jobs->patch($job->getName(), $updateRequest));
            }
            $i++;
        }

        $results = $batchUpdate->execute();
        // Disable batch mode.
        $jobService->getClient()->setUseBatch(false);
        $updatedJobs = array();
        foreach ($results as $result) {
            if ($result instanceof Google_Service_Exception) {
                printf("Update Error Message:\n%s\n", $result->getMessage());
            } else {
                printf("Update Job:\n%s\n", var_export($result, true));
                array_push($updatedJobs, $result);
            }
        }

        return $updatedJobs;
    }
    # [END batch_job_update]

    # [START batch_job_delete]
    /**
     * Deletes jobs in batch.
     *
     * @param Google_Service_JobService_Job[] $jobsToBeDeleted
     */
    public static function batch_delete_jobs(array $jobsToBeDeleted)
    {
        $jobService = self::get_job_service();
        $jobService->getClient()->setUseBatch(true);

        // Creates batch request.
        $batchDelete = $jobService->createBatch();

        foreach ($jobsToBeDeleted as $jobToBeDeleted) {
            $deleteRequest = $jobService->jobs->delete($jobToBeDeleted->getName());
            $batchDelete->add($deleteRequest);
        }
        $results = $batchDelete->execute();
        // Disable batch mode.
        $jobService->getClient()->setUseBatch(false);

        foreach ($results as $result) {
            if ($result instanceof Google_Service_Exception) {
                printf("Delete Error Message:\n%s\n", $result->getMessage());
            } else {
                echo "Job deleted\n";
            }
        }
    }

    # [END batch_job_delete]

    protected function configure()
    {
        $this
            ->setName('batch-operation')
            ->setDescription('Run batch operation sample script.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Create a company.
        $companyName = BasicCompanySample::create_company(BasicCompanySample::generate_company())->getName();

        // Batch create jobs.
        $createdJobs = self::batch_create_jobs($companyName);

        // Batch update jobs.
        $updatedJobs = self::batch_job_update($createdJobs);

        // Batch delete jobs.
        self::batch_delete_jobs($updatedJobs);

        BasicCompanySample::delete_company($companyName);
    }
}
