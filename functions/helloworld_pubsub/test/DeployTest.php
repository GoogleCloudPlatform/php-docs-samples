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

namespace Google\Cloud\Samples\Functions\HelloworldPubsub\Test;

use Google\Cloud\Logging\LoggingClient;
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

    private static $entryPoint = 'helloworldPubsub';

    /* var string */
    private static $projectId;

    /* var string */
    private static $topicName;

    /** @var LoggingClient */
    private static $loggingClient;

    public function dataProvider()
    {
        return [
            [
                'name' => '',
                'expected' => 'Hello, World!',
                'label' => 'Should print a default value'
            ],
                        [
                'name' => 'John',
                'expected' => 'Hello, John!',
                'label' => 'Should print a name'
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHelloworldPubsub(string $name, string $expected, string $label): void
    {
        // Send Pub/Sub message.
        $this->publishMessage($name);

        $fiveMinAgo = date(\DateTime::RFC3339, strtotime('-5 minutes'));
        $this->processFunctionLogs($fiveMinAgo, function (\Iterator $logs) use ($name, $expected, $label) {
            // Concatenate all relevant log messages.
            $actual = '';
            foreach ($logs as $log) {
                $info = $log->info();
                $actual .= $info['textPayload'];
            }

            $this->assertStringContainsString($expected, $actual, $label);
        }, 5, 10);
    }

    private function publishMessage(string $name): void
    {
        // Publish a message to trigger the function.
        $pubsub = new PubSubClient();
        $topic = $pubsub->topic(self::$topicName);
        $topic->publish([
            'data' => $name,
            'attributes' => [
                'foo' => 'bar'
            ]
        ]);
    }

    /**
     * Deploy the Cloud Function, called from DeploymentTrait::deployApp().
     *
     * Overrides CloudFunctionDeploymentTrait::doDeploy().
     */
    private static function doDeploy()
    {
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');
        self::$topicName = self::requireEnv('FUNCTIONS_TOPIC');

        return self::$fn->deploy([], '--trigger-topic=' . self::$topicName);
    }
}
