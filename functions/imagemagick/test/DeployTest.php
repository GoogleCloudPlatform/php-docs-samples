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

declare(strict_types=1);

namespace Google\Cloud\Samples\Functions\ImageMagick\Test;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\TestUtils\CloudFunctionDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\GcloudWrapper\CloudFunction;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestCasesTrait.php';

/**
 * Class DeployTest.
 *
 * This test is not run by the CI system.
 *
 * To skip deployment of a new function, run with "GOOGLE_SKIP_DEPLOYMENT=true".
 * To skip deletion of the tested function, run with "GOOGLE_KEEP_DEPLOYMENT=true".
 */
class DeployTest extends TestCase
{
    use CloudFunctionDeploymentTrait;
    use EventuallyConsistentTestTrait;
    use TestCasesTrait;

    /** @var string */
    private static $entryPoint = 'blurOffensiveImages';

    /** @var string */
    private static $functionSignatureType = 'cloudevent';

    /** @var string */
    // The test starts by copying images from this bucket.
    private const FIXTURE_SOURCE_BUCKET = 'cloud-devrel-public';

    /** @var string */
    // This is the bucket the deployed function monitors.
    // The test copies image from FIXTURE_SOURCE_BUCKET to this one.
    private static $monitoredBucket;

    /** @var string */
    // The function saves any blurred images to this bucket.
    private static $blurredBucket;

    /** @var StorageClient */
    private static $storageClient;

    /** @var LoggingClient */
    private static $loggingClient;

    /**
      * @dataProvider cases
      */
    public function testFunction(
        $cloudevent,
        $label,
        $fileName,
        $expected,
        $statusCode
    ): void {
        // Upload target file.
        $fixtureBucket = self::$storageClient->bucket(self::FIXTURE_SOURCE_BUCKET);
        $object = $fixtureBucket->object($fileName);

        $object->copy(self::$monitoredBucket, ['name' => $fileName]);

        // Give event and log systems a head start.
        // If log retrieval fails to find logs for our function within retry limit, increase sleep time.
        sleep(5);

        $fiveMinAgo = date(\DateTime::RFC3339, strtotime('-5 minutes'));
        $this->processFunctionLogs(self::$fn, $fiveMinAgo, function (\Iterator $logs) use ($expected, $label) {
            // Concatenate all relevant log messages.
            $actual = '';
            foreach ($logs as $log) {
                $info = $log->info();
                $actual .= $info['textPayload'];
            }

            // Only testing one property to decrease odds the expected logs are
            // split between log requests.
            $this->assertContains($expected, $actual, $label . ':');
        });
    }

    /**
     * Retrieve and process logs for the defined function.
     *
     * @param CloudFunction $fn function whose logs should be checked.
     * @param string $startTime RFC3339 timestamp marking start of time range to retrieve.
     * @param callable $process callback function to run on the logs.
     */
    private function processFunctionLogs(CloudFunction $fn, string $startTime, callable $process)
    {
        $projectId = self::requireEnv('GOOGLE_PROJECT_ID');

        if (empty(self::$loggingClient)) {
            self::$loggingClient = new LoggingClient([
                'projectId' => $projectId
            ]);
        }

        // Define the log search criteria.
        $logFullName = 'projects/' . $projectId . '/logs/cloudfunctions.googleapis.com%2Fcloud-functions';
        $filter = sprintf(
            'logName="%s" resource.labels.function_name="%s" timestamp>="%s"',
            $logFullName,
            $fn->getFunctionName(),
            $startTime
        );

        echo "\nRetrieving logs [$filter]...\n";

        // Check for new logs for the function.
        $attempt = 1;
        $this->runEventuallyConsistentTest(function () use ($filter, $process, &$attempt) {
            $entries = self::$loggingClient->entries(['filter' => $filter]);
 
            // If no logs came in try again.
            if (empty($entries->current())) {
                echo 'Logs not found, attempting retry #' . $attempt++ . PHP_EOL;
                throw new ExpectationFailedException('Log Entries not available');
            }
            echo 'Processing logs...' . PHP_EOL;

            $process($entries);
        }, $retries = 10);
    }

    /**
     * Deploy the Function.
     *
     * Overrides CloudFunctionLocalTestTrait::doDeploy().
     */
    private static function doDeploy()
    {
        // Initialize variables
        if (empty(self::$monitoredBucket)) {
            self::$monitoredBucket = self::requireEnv('FUNCTIONS_BUCKET');
        }
        if (empty(self::$blurredBucket)) {
            self::$blurredBucket = self::requireEnv('BLURRED_BUCKET_NAME');
        }

        if (empty(self::$storageClient)) {
            self::$storageClient = new StorageClient();
        }

        // Forward required env variables to Cloud Functions.
        $envVars = 'FUNCTIONS_BUCKET=' . self::$monitoredBucket . ',';
        $envVars .= 'BLURRED_BUCKET_NAME=' . self::$blurredBucket;

        self::$fn->deploy(
            ['--update-env-vars' => $envVars],
            '--trigger-bucket=' . self::$monitoredBucket
        );
    }
}
