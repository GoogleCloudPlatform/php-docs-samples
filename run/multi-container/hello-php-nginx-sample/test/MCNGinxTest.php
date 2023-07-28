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

    /** @var \Google\Cloud\TestUtils\GcloudWrapper\CloudRun */
    private static $nginxService;
    private static $phpService;

    /** @var string */
    private static $projectId;
    private static $region;
    private static $versionId;
    private static $repoName;
    private static $nginxImage;
    private static $appImage;

    /**
     * Set up Artifact Registry image tags refs and declare Cloud Run services
     */
    public static function setUpDeploymentVars()
    {
        $projectId = self::requireEnv('GOOGLE_PROJECT_ID');
        $region = getenv('REGION') ?: 'us-west1';
        $repoName = getenv('REPO_NAME') ?: 'cloud-run-source-deploy';
        $versionId = getenv('GOOGLE_VERSION_ID') ?: sprintf('hellophpnginx-%s', time());

        // Declaring Cloud Run services
        self::$nginxService = new CloudRun(self::$projectId, ['service' => $versionId]);
        self::$phpService = new CloudRun(self::$projectId, ['service' => $versionId]);

        // Declaring Cloud Build image tags
        self::$nginxImage = sprintf('%s-docker.pkg.dev/%s/%s/nginx', $region, self::$projectId, $repoName);
        self::$appImage = sprintf('%s-docker.pkg.dev/%s/%s/php', $region, self::$projectId, $repoName);
    }

    private static function beforeDeploy()
    {
        self::setUpDeploymentVars();

        // Suppress gcloud prompts during deployment.
        putenv('CLOUDSDK_CORE_DISABLE_PROMPTS=1');
    }

    /**
     * Deploy the Cloud Run services (nginx, php app)
     */
    private static function doDeploy()
    {
        // Build & deploy nginx container image
        if (false === self::$nginxService->build(self::$nginxImage, [], '../nginx')) {
            return false;
        }
        if (false === self::$nginxService->deploy(self::$nginxImage)) {
            return false;
        }

        // Build & deploy php app container image
        if (false === self::$phpService->build(self::$appImage, [], '../php-app')) {
            return false;
        }
        if (false === self::$phpService->deploy(self::$appImage)) {
            return false;
        }

        return true;
    }

    /**
     * Delete a deployed Cloud Run service.
     */
    private static function doDelete()
    {
        // Delete nginx Cloud Run service
        self::$nginxService->delete();
        self::$nginxService->deleteImage(self::$nginxImage);

        // Delete hello php Cloud Run service
        self::$phpService->delete();
        self::$phpService->deleteImage(self::$appImage);
    }

    public function testService()
    {
        $targetAudience = self::getBaseUri();

        // create middleware
        $middleware = ApplicationDefaultCredentials::getIdTokenMiddleware($targetAudience);
        $stack = HandlerStack::create();
        $stack->push($middleware);

        // create the HTTP client
        $client = new Client([
            'handler' => $stack,
            'auth' => 'google_auth',
            'base_uri' => $targetAudience,
        ]);

        // Run the test.
        $resp = $client->get('/');
        $this->assertEquals('200', $resp->getStatusCode());
        $this->assertEquals('Hello World!', (string) $resp->getBody());
    }

    public function getBaseUri()
    {
        return self::$phpService->getBaseUrl();
    }
}
