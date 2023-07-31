<?php
/**
 * Copyright 2023 Google LLC.
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

namespace Google\Cloud\Samples\Run\MCHelloPHPNginx;

use Google\Cloud\TestUtils\DeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\GcloudWrapper\CloudRun;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class MCNginxTest.
 * @group deploy
 */
class MCNginxTest extends TestCase
{
    use DeploymentTrait;
    use EventuallyConsistentTestTrait;
    use TestTrait;

    /** @var string */
    private static $projectId;
    private static $region;
    private static $repoName;
    private static $mcServiceName;
    private static $mcService;
    private static $nginxImage;
    private static $appImage;


    /**
     * Execute given cmd
     *
     * Note: The use of exec() outside of the usual CloudRun stub class deploy() pattern is intentional
     * as the feature is in public preview. If more php multi-container samples get added, this should be
     * refactored to make `gcloud run services replace ...` reusable across samples
     */
    private static function execCmd($cmd = '', $output = null, $retvalue = 3)
    {
        if (false === exec($cmd, $output, $retvalue)) {
            return false;
        }
        return true;
    }

    /**
     * Set up test leveraged variabled and establish Artifact Registry image tags refs
     */
    public static function setUpDeploymentVars()
    {
        $timestamp = time();
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');
        self::$region = getenv('REGION') ?: 'us-west1';
        self::$repoName = getenv('REPO_NAME') ?: 'cloud-run-source-deploy';
        self::$mcServiceName = sprintf('hello-php-nginx-mc-%s', $timestamp);
        $versionId = getenv('GOOGLE_VERSION_ID') ?: self::$mcServiceName;

        self::$mcService = new CloudRun(self::$projectId, ['service' => $versionId]);

        // Declaring Cloud Build image tags
        self::$nginxImage = sprintf('%s-docker.pkg.dev/%s/%s/nginx', self::$region, self::$projectId, self::$repoName);
        self::$appImage = sprintf('%s-docker.pkg.dev/%s/%s/php', self::$region, self::$projectId, self::$repoName);
    }

    private static function beforeDeploy()
    {
        self::setUpDeploymentVars();
        self::buildImages();
        self::doYamlSubstitution();

        // Suppress gcloud prompts during deployment.
        putenv('CLOUDSDK_CORE_DISABLE_PROMPTS=1');
    }

    /**
     * Build nginx + hello php container images
     */
    private static function buildImages()
    {
        if (false === self::$mcService->build(self::$nginxImage, [], './nginx')) {
            return false;
        }
        if (false === self::$mcService->build(self::$appImage, [], './php-app')) {
            return false;
        }
    }

    /**
     * Execute yaml substitution
     */
    private static function doYamlSubstitution()
    {
        // Execute yaml substitution
        $subCmd = sprintf('sed -i -e s/MC_SERVICE_NAME/%s/g -e s/REGION/%s/g -e s/REPO_NAME/%s/g -e s/PROJECT_ID/%s/g service.yaml', self::$mcServiceName, self::$region, self::$repoName, self::$projectId);

        return self::execCmd($subCmd);
    }

    /**
     * Deploy the Cloud Run services (nginx, php app)
     */
    private static function doDeploy()
    {
        // Execute multi-container service deployment
        $mcCmd = sprintf('gcloud run services replace service.yaml --region %s --quiet', self::$region);

        return self::execCmd($mcCmd);
    }

    /**
     * Delete a deployed Cloud Run MC service and related images.
     */
    private static function doDelete()
    {
        self::$mcService->delete();
        self::$mcService->deleteImage(self::$nginxImage);
        self::$mcService->deleteImage(self::$appImage);
    }

    /**
     * Test that the multi-container is running with both
     * serving and sidecar running as expected
     */
    public function testService()
    {
        $mcUrl = self::execCmd(sprintf('gcloud run services describe %s --region %s --format "value(status.url)"', self::$mcServiceName, self::$region));
        $mcStatus = self::execCmd(sprintf('gcloud run services describe %s --region %s --format "value(status.conditions[0].type)"', self::$mcServiceName, self::$region));

        echo "=====url=====";
        echo "${mcUrl}";
        echo "${mcStatus}";
        echo "==========";

        if (empty($mcUrl) or empty($mcStatus)) {
            return false;
        }

        # Checking that fpm (FastCGI Process Manager)
        $mcNginxLog = exec('gcloud logging read "resource.type=cloud_run_revision AND resource.labels.service_name=%s AND labels.container_name=nginx" | grep -e "Default STARTUP TCP probe succeeded after 1 attempt for container nginx"', self::$mcServiceName);
        # Check Cloud Run MC nginx & hellophp logs for signs of successful deployment and connection
        $mcHelloPHPLog = exec('gcloud logging read "resource.type=cloud_run_revision AND resource.labels.service_name=%s AND labels.container_name=hellophp" | grep -e "NOTICE: fpm is running, pid 1"', self::$mcServiceName);

        echo "=====log=====";
        echo "${mcNginxLog}";
        echo "${mcHelloPHPLog}";
        echo "==========";

        // Validate that the expected logs have been recovered
        if (empty($mcNginxLog) or empty($mcHelloPHPLog)) {
            return false;
        }

        return true;
    }

    public function getBaseUri()
    {
        return self::$mcService->getBaseUrl();
    }
}
