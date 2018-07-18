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
use Google_Service_JobService_Company;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This file contains the basic knowledge about company and job, including:
 *
 * - Construct a company with required fields
 *
 * - Create a company
 *
 * - Get a company
 *
 * - Update a company
 *
 * - Update a company with field mask
 *
 * - Delete a company
 */
final class BasicCompanySample extends Command
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

    # [START basic_company]

    /**
     * Generates a company.
     *
     * @return Google_Service_JobService_Company
     */
    public static function generate_company()
    {
        $distributorCompanyId = 'company:' . rand();

        $company = new Google_Service_JobService_Company();
        $company->setDisplayName('Google');
        $company->setHqLocation('1600 Amphitheatre Parkway Mountain View, CA 94043');
        $company->setDistributorCompanyId($distributorCompanyId);

        printf("Company generated:\n%s\n", var_export($company, true));
        return $company;
    }
    # [END basic_company]

    # [START create_company]
    /**
     * Creates a company in Google Cloud Job Discovery.
     *
     * @param Google_Service_JobService_Company $companyToBeCreated
     * @return Google_Service_JobService_Company
     */
    public static function create_company(Google_Service_JobService_Company $companyToBeCreated)
    {
        $jobService = self::get_job_service();

        $companyCreated = $jobService->companies->create($companyToBeCreated);
        printf("Company created:\n%s\n", var_export($companyCreated, true));
        return $companyCreated;
    }
    # [END create_company]

    # [START get_company]
    /**
     * Gets a company by its name.
     *
     * @param string $companyName
     * @return Google_Service_JobService_Company
     */
    public static function get_company($companyName)
    {
        $jobService = self::get_job_service();

        $companyExisted = $jobService->companies->get($companyName);
        printf("Company existed:\n%s\n", var_export($companyExisted, true));
        return $companyExisted;
    }
    # [END get_company]

    # [START update_company]
    /**
     * Updates a company.
     *
     * @param string $companyName
     * @param Google_Service_JobService_Company $companyToBeUpdated
     * @return Google_Service_JobService_Company
     */
    public static function update_company($companyName, Google_Service_JobService_Company $companyToBeUpdated)
    {
        $jobService = self::get_job_service();

        $companyUpdated = $jobService->companies->patch($companyName, $companyToBeUpdated);
        printf("Company updated:\n%s\n", var_export($companyUpdated, true));
        return $companyUpdated;
    }
    # [END update_company]

    # [START update_company_with_field_mask]
    /**
     * Updates a company with field mask.
     *
     * @param string $companyName
     * @param string $fieldMask
     * @param Google_Service_JobService_Company $companyToBeUpdated
     * @return Google_Service_JobService_Company
     */
    public static function update_company_with_field_mask(
        $companyName,
        $fieldMask,
        Google_Service_JobService_Company $companyToBeUpdated
    ) {
        $jobService = self::get_job_service();

        $optParams = array('updateCompanyFields' => $fieldMask);
        $companyUpdated = $jobService->companies->patch($companyName, $companyToBeUpdated, $optParams);
        printf("Company updated:\n%s\n", var_export($companyUpdated, true));
        return $companyUpdated;
    }
    # [END update_company_with_field_mask]

    # [START delete_company]
    /**
     * Deletes a company.
     *
     * @param string $companyName
     */
    public static function delete_company($companyName)
    {
        $jobService = self::get_job_service();

        $jobService->companies->delete($companyName);
        echo 'Company deleted' . PHP_EOL;
    }

    # [END delete_company]

    protected function configure()
    {
        $this
            ->setName('basic-company')
            ->setDescription('Run basic company sample script to create, update, and delete a company.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Construct a company.
        $companyToBeCreated = self::generate_company();

        // Create a company.
        $companyCreated = self::create_company($companyToBeCreated);

        // Get a company
        $companyName = $companyCreated->getName();
        self::get_company($companyName);

        // Update a company
        $companyToBeUpdated = clone $companyCreated;
        $companyToBeUpdated->setWebsite("https://elgoog.im");
        self::update_company($companyName, $companyToBeUpdated);

        // Update a company with field mask
        $companyToBeUpdated = new Google_Service_JobService_Company();
        $companyToBeUpdated->setDisplayName("changedTitle");
        $companyToBeUpdated->setDistributorCompanyId($companyCreated->getDistributorCompanyId());
        self::update_company_with_field_mask($companyName, 'displayName', $companyToBeUpdated);

        self::delete_company($companyName);
    }
}
