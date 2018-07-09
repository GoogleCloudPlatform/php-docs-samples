<?php
/**
 * Created by PhpStorm.
 * User: yuyuyu
 * Date: 7/9/18
 * Time: 2:10 PM
 */

namespace Google\Cloud\Samples\Jobs;


use Google_Client;
use Google_Service_JobService;

final class JobServiceConnector
{

    private static $jobService;

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

    public static function get_job_service()
    {
        if (!isset($jobService)) {
            $jobService = self::create_job_service();
        }
        return $jobService;
    }
}