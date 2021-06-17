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

namespace Google\Cloud\Samples\Functions\TipsRetry\Test;

use Google\Cloud\TestUtils\CloudFunctionDeploymentTrait;
use Google\Cloud\PubSub\PubSubClient;
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

    private static $entryPoint = 'tipsRetry';

    /* var string */
    private static $topicName;

    public function testTipsRetry(): void
    {
        // Send Pub/Sub message.
        $this->publishMessage();

        $fiveMinAgo = date(\DateTime::RFC3339, strtotime('-5 minutes'));
        $this->processFunctionLogs($fiveMinAgo, function (\Iterator $logs) {
            // Concatenate all relevant log messages.
            $actual = '';
            foreach ($logs as $log) {
                $info = $log->info();
                $actual .= $info['textPayload'];
            }

            // Check that multiple invocations of the function have occurred.
            $retryText = 'Intermittent failure occurred; retrying...';
            $retryCount = substr_count($actual, $retryText);
            $this->assertGreaterThan(1, $retryCount);
        }, 4, 30);
    }

    private function publishMessage(): void
    {
        // Construct Pub/Sub message
        $message = json_encode(['some_parameter' => true]);

        // Publish a message to the function.
        $pubsub = new PubSubClient();
        $topic = $pubsub->topic(self::$topicName);
        $topic->publish(['data' => $message]);
    }

    /**
     * Deploy the Cloud Function, called from DeploymentTrait::deployApp().
     *
     * Overrides CloudFunctionDeploymentTrait::doDeploy().
     */
    private static function doDeploy()
    {
        self::$topicName = self::requireEnv('FUNCTIONS_TOPIC');

        /**
         * The --retry flag tells Cloud Functions to automatically retry
         * failed function invocations. This is necessary because we're
         * the parent sample exists to demonstrate automatic retries.
         */
        return self::$fn->deploy(
            ['--retry' => ''],
            '--trigger-topic=' . self::$topicName
        );
    }
}
