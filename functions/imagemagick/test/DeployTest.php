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
use Google\Cloud\TestUtils\CloudFunctionDeploymentTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestCasesTrait.php';

/**
 * Class DeployTest.
 *
 * This test is not run by the CI system.
 *
 * To skip deployment of a new function, run with "GOOGLE_SKIP_DEPLOYMENT=true".
 * To skip deletion of the tested function, run with "GOOGLE_KEEP_DEPLOYMENT=true".
 * @group deploy
 */
class DeployTest extends TestCase
{
    use CloudFunctionDeploymentTrait;
    use TestCasesTrait;

    // The test uses this bucket to copy images.
    private const FIXTURE_SOURCE_BUCKET = 'cloud-devrel-public';

    /**
     * This is the bucket the deployed function monitors.
     * The test copies image from FIXTURE_SOURCE_BUCKET to this one.
     */
    private static $monitoredBucket;

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
        // Trigger the cloud storage event by copying the image over
        $storageClient = new StorageClient();
        $fixtureBucket = $storageClient->bucket(self::FIXTURE_SOURCE_BUCKET);

        $object = $fixtureBucket->object($fileName);
        $object->copy(self::$monitoredBucket, ['name' => $fileName]);

        $fiveMinAgo = date(\DateTime::RFC3339, strtotime('-5 minutes'));
        $this->processFunctionLogs($fiveMinAgo, function (\Iterator $logs) use ($expected, $label) {
            // Concatenate all relevant log messages.
            $actual = '';
            foreach ($logs as $log) {
                $info = $log->info();
                $actual .= $info['textPayload'];
            }

            // Only testing one property to decrease odds the expected logs are
            // split between log requests.
            $this->assertStringContainsString($expected, $actual, $label . ':');
        }, 6, 30);
    }

    /**
     * Deploy the Function.
     *
     * Overrides CloudFunctionLocalTestTrait::doDeploy().
     */
    private static function doDeploy()
    {
        // Initialize variables
        self::$monitoredBucket = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        $blurredBucket = self::requireEnv('BLURRED_BUCKET_NAME');

        // Forward required env variables to Cloud Functions.
        $envVars = sprintf('BLURRED_BUCKET_NAME=%s', $blurredBucket);

        self::$fn->deploy(
            ['--update-env-vars' => $envVars],
            '--trigger-bucket=' . self::$monitoredBucket
        );
    }
}
