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

namespace Google\Cloud\Samples\Functions\FirebaseReactive\Test;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\TestUtils\CloudFunctionDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
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
    private static $entryPoint = 'firebaseReactive';

    /** @var string */
    private static $functionSignatureType = 'cloudevent';

    /** @var string */
    private static $collectionName = 'messages';

    /** @var string */
    private static $documentName = 'taco';

    /** @var LoggingClient */
    private static $loggingClient;

    /** @var FirestoreClient */
    private static $firestoreClient;

    /**
     * Override the default project ID set by CloudFunctionDeploymentTrait.
     */
    private static function checkProjectEnvVars()
    {
        if (empty(self::$projectId)) {
            self::$projectId = self::requireOneOfEnv([
                'FIRESTORE_PROJECT_ID',
                'GOOGLE_PROJECT_ID'
            ]);
        }
    }

    /**
     * Deploy the Cloud Function, called from DeploymentTrait::deployApp().
     *
     * Overrides CloudFunctionDeploymentTrait::doDeploy().
     */
    private static function doDeploy()
    {
        $resource = sprintf(
            'projects/%s/databases/(default)/documents/%s/%s',
            self::$projectId,
            self::$collectionName,
            self::$documentName
        );
        $event = 'providers/cloud.firestore/eventTypes/document.write';

        return self::$fn->deploy([
            '--trigger-resource' => $resource,
            '--trigger-event' => $event
        ], '');
    }

    public function dataProvider()
    {
        $data = uniqid();
        $expected = 'Replacing value: ' . $data . ' --> ' . strtoupper($data);
        return [
            [
                'data' => ['original' => $data],
                'expected' => $expected
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFirebaseReactive(array $data, string $expected): void
    {
        // Trigger storage upload.
        $objectUri = $this->updateFirestore(
            self::$collectionName,
            self::$documentName,
            $data
        );

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
        }, 5, 30);
    }

    /**
     * Update a value in Firebase Realtime Database (RTDB).
     *
     * @param string $document The Firestore document to modify.
     * @param string $collection The Firestore collection to modify.
     * @param string $data The key-value pair to set the specified collection to.
     *
     * @throws \RuntimeException
     */
    private function updateFirestore(
        string $document,
        string $collection,
        array $data
    ): void {
        if (empty(self::$firestoreClient)) {
            self::$firestoreClient = new FirestoreClient(
                ['projectId' => self::$projectId]
            );
        }

        self::$firestoreClient
            ->collection(self::$collectionName)
            ->document(self::$documentName)
            ->set($data);
    }
}
