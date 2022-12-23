<?php
/**
 * Copyright 2021 Google LLC.
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

namespace Google\Cloud\Samples\Functions\FirebaseAuth\Test;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\CredentialsLoader;
use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\TestUtils\CloudFunctionDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class DeployTest.
 * @group deploy
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

    /** @var string */
    private static $entryPoint = 'firebaseAuth';

    /** @var string */
    private static $functionSignatureType = 'cloudevent';

    /** @var LoggingClient */
    private static $loggingClient;

    /** @var \GuzzleHttp\Client */
    private static $apiHttpClient;

    /**
     * Deploy the Cloud Function, called from DeploymentTrait::deployApp().
     *
     * Overrides CloudFunctionDeploymentTrait::doDeploy().
     */
    private static function doDeploy()
    {
        $event = 'providers/firebase.auth/eventTypes/user.create';

        return self::$fn->deploy([
            '--trigger-event' => $event
        ], '');
    }

    public function dataProvider()
    {
        $email = uniqid();
        return [
            [
                'label' => 'Listens to Auth events',
                'email' => $email . '@example.com',
                'expected' => $email . '@example.com'
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFirebaseAuth(
        string $label,
        string $email,
        string $expected
    ): void {
        // Trigger user creation.
        $this->createAuthUser($email);

        // Give event and log systems a head start.
        // If log retrieval fails to find logs for our function within retry limit, increase sleep time.
        sleep(10);

        $fiveMinAgo = date(\DateTime::RFC3339, strtotime('-5 minutes'));
        $this->processFunctionLogs($fiveMinAgo, function (\Iterator $logs) use ($expected, $label) {
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
            $this->assertStringContainsString($expected, $actual, $label);
        });
    }

    /**
     * Create a new Firebase Auth user.
     *
     * @param string $email The key to update.
     * @param string $value The value to set the key to.
     *
     * @throws \RuntimeException
     */
    private function createAuthUser(string $email): void
    {
        if (empty(self::$apiHttpClient)) {
            $credentials = ApplicationDefaultCredentials::getCredentials('https://www.googleapis.com/auth/cloud-platform');
            self::$apiHttpClient = CredentialsLoader::makeHttpClient($credentials, [
                'base_uri' => 'https://identitytoolkit.googleapis.com/'
            ]);
        }

        // Create the account
        $createResponse = (string) self::$apiHttpClient->post('/v1/accounts:signUp', [
            'headers' => ['If-Match' => '*'],
            'json' => [
                'email' => $email,
                'password' => uniqid(),
                'returnSecureToken' => true
            ]
        ])->getBody();

        $idToken = json_decode($createResponse, true)['localId'];

        // Delete the account (to clean up after the test)
        self::$apiHttpClient->post('/v1/accounts:delete', [
            'headers' => ['If-Match' => '*'],
            'json' => [
                'localId' => $idToken
            ]
        ]);
    }
}
