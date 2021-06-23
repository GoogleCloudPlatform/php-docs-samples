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

namespace Google\Cloud\Samples\Functions\FirebaseRTDB\Test;

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\TestUtils\CloudFunctionDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

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
    use EventuallyConsistentTestTrait;

    /** @var string */
    private static $entryPoint = 'firebaseRTDB';

    /** @var string */
    private static $functionSignatureType = 'cloudevent';

    /** @var string */
    private static $rtdbPath = 'foods';

    /** @var LoggingClient */
    private static $loggingClient;

    /** @var Database */
    private static $database;

    /**
     * Deploy the Cloud Function, called from DeploymentTrait::deployApp().
     *
     * Overrides CloudFunctionDeploymentTrait::doDeploy().
     */
    private static function doDeploy()
    {
        // self::projectId is undefined
        $projectId = self::requireEnv('GOOGLE_PROJECT_ID');

        $resource = sprintf(
            'projects/_/instances/%s/refs/%s',
            $projectId,
            self::$rtdbPath
        );
        $event = 'providers/google.firebase.database/eventTypes/ref.write';

        return self::$fn->deploy([
            '--trigger-resource' => $resource,
            '--trigger-event' => $event
        ], '');
    }

    public function dataProvider()
    {
        $data = ['taco' => (string) uniqid()];
        return [
            [
                'data' => $data,
                'expected' => json_encode($data)
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFirebaseRTDB(array $data, string $expected): void
    {
        // Trigger storage upload.
        $objectUri = $this->updateRTDB(self::$rtdbPath, $data);

        $fiveMinAgo = date(\DateTime::RFC3339, strtotime('-5 minutes'));
        $this->processFunctionLogs($fiveMinAgo, function (\Iterator $logs) use ($expected) {
            // Concatenate all relevant log messages.
            $actual = '';
            foreach ($logs as $log) {
                $info = $log->info();
                if (isset($info['textPayload'])) {
                    $actual .= $info['textPayload'];
                }
            }

            // Only testing one property to decrease odds the expected logs are
            // split between log requests.
            $this->assertStringContainsString($expected, $actual);
        }, 5, 10);
    }

    /**
     * Update a value in Firebase Realtime Database (RTDB).
     *
     * @param string $path Path of the RTDB attribute to set.
     * @param string $data Data to upload as an object..
     *
     * @throws \RuntimeException
     */
    private function updateRTDB(string $path, array $data): void
    {
        $client = new Client([
            'base_uri' => sprintf('https://%s.firebaseio.com', self::$projectId)
        ]);

        $url = '/' . $path . '.json';
        $url_response = $client->put($url, [
            'json' => $data
        ]);
    }
}
