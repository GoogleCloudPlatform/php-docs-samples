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
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\GcloudWrapper\CloudFunction;
use Google\Cloud\PubSub\PubSubClient;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\ExpectationFailedException;

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

        // Give event and log systems a head start.
        // If log retrieval fails to find logs for our function within retry limit, increase sleep time.
        sleep(60);

        $fiveMinAgo = date(\DateTime::RFC3339, strtotime('-5 minutes'));
        $this->processFunctionLogs(self::$fn, $fiveMinAgo, function (\Iterator $logs) use ($name, $expected, $label) {
            // Concatenate all relevant log messages.
            $actual = '';
            foreach ($logs as $log) {
                $info = $log->info();
                $actual .= $info['textPayload'];
            }

            $expected = 'Hello, ' . $name . '!';
            $this->assertContains($expected, $actual, $label);
        });
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
     * Deploy the Cloud Function, called from DeploymentTrait::deployApp().
     *
     * Overrides CloudFunctionDeploymentTrait::doDeploy().
     */
    private static function doDeploy()
    {
        self::$projectId = self::requireEnv('GOOGLE_CLOUD_PROJECT');
        self::$topicName = self::requireEnv('FUNCTIONS_TOPIC');

        return self::$fn->deploy([], '--trigger-topic=' . self::$topicName);
    }
}
