<?php
/**
 * Copyright 2020 Google LLC.
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

namespace Google\Cloud\Samples\Run\Helloworld;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Cloud\TestUtils\DeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\GcloudWrapper\CloudRun;
use Google\Cloud\TestUtils\TestTrait;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;

/**
 * Class DeployTest.
 * @group deploy
 */
class DeloyTest extends TestCase
{
    use DeploymentTrait;
    use EventuallyConsistentTestTrait;
    use TestTrait;

    /** @var \Google\Cloud\TestUtils\GcloudWrapper\CloudRun */
    private static $service;

    /** @var string */
    private static $image;

    /**
     * Deploy the application.
     */
    public static function setUpDeploymentVars()
    {
        if (empty(self::$projectId)) {
            self::checkProjectEnvVars();
        }

        $versionId = getenv('GOOGLE_VERSION_ID') ?: sprintf('helloworld-%s', time());
        self::$service = new CloudRun(self::$projectId, ['service' => $versionId]);
        self::$image = sprintf('gcr.io/%s/%s:latest', self::$projectId, $versionId);
    }

    private static function beforeDeploy()
    {
        // Ensure setUpDeploymentVars has been called
        if (is_null(self::$service)) {
            self::setUpDeploymentVars();
        }

        // Suppress gcloud prompts during deployment.
        putenv('CLOUDSDK_CORE_DISABLE_PROMPTS=1');
    }

    /**
     * Deploy the Cloud Run service.
     */
    private static function doDeploy()
    {
        if (false === self::$service->build(self::$image)) {
            return false;
        }

        if (false === self::$service->deploy(self::$image)) {
            return false;
        }

        return true;
    }

    /**
     * Delete a deployed Cloud Run service.
     */
    private static function doDelete()
    {
        self::$service->delete();
        self::$service->deleteImage(self::$image);
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
        return self::$service->getBaseUrl();
    }
}
