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

use Google\Auth\ApplicationDefaultCredentials;
use Google\Cloud\TestUtils\DeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\GcloudWrapper\CloudRun;
use Google\Cloud\TestUtils\TestTrait;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
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
     * Execute given bash cmd
     * Note: Since this test requires more custom gcloud command executions,
     * implemented this function outside of using the usual CloudRun stub class deploy() pattern.
     * If more php multi-container samples are added, this should be
     * refactored to make `gcloud run services replace ...` reusable across samples.
     */
    private static function execCmd($cmd = '', $retvalue = null)
    {
        $output = null;
        exec($cmd, $output, $retvalue);

        // Returns the first resulting output of cmd
        return $output[0];
    }

    /**
     * Set up test leveraged variabled and establish Artifact Registry image tags refs
     */
    public static function setUpDeploymentVars()
    {
        // Instantiate required private vars
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');
        self::$region = getenv('REGION') ?: 'us-west1';
        self::$repoName = getenv('REPO_NAME') ?: 'cloud-run-source-deploy';
        self::$mcServiceName = sprintf('hello-php-nginx-mc-%s', time());

        // Declare CloudRun stub instance for multi-container service
        self::$mcService = new CloudRun(self::$projectId, ['region' => self::$region, 'service' => self::$mcServiceName]);

        // Declaring Cloud Build image tags
        self::$nginxImage = sprintf('%s-docker.pkg.dev/%s/%s/nginx', self::$region, self::$projectId, self::$repoName);
        self::$appImage = sprintf('%s-docker.pkg.dev/%s/%s/php', self::$region, self::$projectId, self::$repoName);
    }

    /**
     * Execute yaml substitution
     */
    private static function doYamlSubstitution()
    {
        $subCmd = sprintf(
            'sed -i -e s/MC_SERVICE_NAME/%s/g -e s/REGION/%s/g -e s/REPO_NAME/%s/g -e s/PROJECT_ID/%s/g service.yaml',
            self::$mcServiceName,
            self::$region,
            self::$repoName,
            self::$projectId
        );

        return self::execCmd($subCmd);
    }

    /**
     * Build both nginx + hello php container images
     * Return true/false if image builds were successful
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
     * Instatiate and build necessary resources
     * required before multi-container deployment
     */
    private static function beforeDeploy()
    {
        self::setUpDeploymentVars();
        self::buildImages();
        self::doYamlSubstitution();

        // Suppress gcloud prompts during deployment.
        putenv('CLOUDSDK_CORE_DISABLE_PROMPTS=1');
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
        $baseUri = self::getBaseUri();
        $mcStatusCmd = sprintf(
            'gcloud run services describe %s --region %s --format "value(status.conditions[0].type)"',
            self::$mcServiceName,
            self::$region
        );
        $mcStatus = self::execCmd($mcStatusCmd);

        if (empty($baseUri) or $mcStatus != 'Ready') {
            return false;
        }

        // create middleware
        $middleware = ApplicationDefaultCredentials::getIdTokenMiddleware($baseUri);
        $stack = HandlerStack::create();
        $stack->push($middleware);

        // create the HTTP client
        $client = new Client([
            'handler' => $stack,
            'auth' => 'google_auth',
            'base_uri' => $baseUri,
        ]);

        // Check that the page renders default phpinfo html and indications it is the main php app
        $resp = $client->get('/');
        $this->assertEquals('200', $resp->getStatusCode());
        $this->assertStringContainsString('This is main php app. Hello PHP World!', (string) $resp->getBody());
    }

    /**
     * Retrieve Cloud Run multi-container service url
     */
    public function getBaseUri()
    {
        $mcUrlCmd = sprintf(
            'gcloud run services describe %s --region %s --format "value(status.url)"',
            self::$mcServiceName,
            self::$region
        );
        $mcUrl = self::execCmd($mcUrlCmd);

        return $mcUrl;
    }
}
