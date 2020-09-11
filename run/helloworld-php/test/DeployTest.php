<?php

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
     *
     * @beforeClass
     */
    public static function setUpDeploymentVars()
    {
        $projectId = self::requireEnv('GOOGLE_PROJECT_ID');
        $versionId = self::requireEnv('GOOGLE_VERSION_ID');
        self::$service = new CloudRun($projectId, ['service' => $versionId]);
        self::$image = sprintf('gcr.io/%s/%s:latest', $projectId, $versionId);
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
