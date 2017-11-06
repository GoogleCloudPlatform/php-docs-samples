<?php
/**
 * Copyright 2016 Google Inc.
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

namespace Google\Cloud\Samples\Monitoring;

use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\ErrorReporting\V1beta1\ErrorStatsServiceClient;
use Google\Devtools\Clouderrorreporting\V1beta1\QueryTimeRange;
use Google\Devtools\Clouderrorreporting\V1beta1\QueryTimeRange_Period;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class error_reportingTest extends \PHPUnit_Framework_TestCase
{
    use EventuallyConsistentTestTrait;

    private static $projectId;

    public static function setUpBeforeClass()
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            self::markTestSkipped('No application credentials were found');
        }
        if (!self::$projectId = getenv('GOOGLE_PROJECT_ID')) {
            self::markTestSkipped('GOOGLE_PROJECT_ID must be set.');
        }
    }

    public function setUp()
    {
        $this->eventuallyConsistentRetryCount = 10;
    }

    public function testReportErrorSimple()
    {
        $message = sprintf('Test Report Error Simple (%s)', date('Y-m-d H:i:s'));
        $output = $this->runCommand('report-simple', [
            'message' => $message,
        ]);
        $this->assertEquals('Reported an error to Stackdriver' . PHP_EOL, $output);

        $errorStats = new ErrorStatsServiceClient();
        $projectName = $errorStats->formatProjectName(self::$projectId);
        $timeRange = (new QueryTimeRange())
            ->setPeriod(QueryTimeRange_Period::PERIOD_1_HOUR);

        // Iterate through all elements
        $this->runEventuallyConsistentTest(function () use (
            $errorStats,
            $projectName,
            $timeRange,
            $message
        ) {
            $messages = [];
            $response = $errorStats->listGroupStats($projectName, $timeRange);
            foreach ($response->iterateAllElements() as $groupStat) {
                $response = $errorStats->listEvents($projectName, $groupStat->getGroup()->getGroupId());
                foreach ($response->iterateAllElements() as $event) {
                    $messages[] = $event->getMessage();
                }
            }

            $this->assertContains(
                $message,
                implode("\n", $messages)
            );
        });
    }

    public function testReportErrorManually()
    {
        $message = sprintf('Test Report Error Manually (%s)', date('Y-m-d H:i:s'));
        $output = $this->runCommand('report', [
            'message' => $message,
            '--user' => 'unittests@google.com',
        ]);
        $this->assertEquals('Reported an error to Stackdriver' . PHP_EOL, $output);

        $errorStats = new ErrorStatsServiceClient();
        $projectName = $errorStats->formatProjectName(self::$projectId);
        $timeRange = (new QueryTimeRange())
            ->setPeriod(QueryTimeRange_Period::PERIOD_1_HOUR);

        // Iterate through all elements
        $this->runEventuallyConsistentTest(function () use (
            $errorStats,
            $projectName,
            $timeRange,
            $message
        ) {
            $messages = [];
            $response = $errorStats->listGroupStats($projectName, $timeRange);
            foreach ($response->iterateAllElements() as $groupStat) {
                $response = $errorStats->listEvents($projectName, $groupStat->getGroup()->getGroupId());
                foreach ($response->iterateAllElements() as $event) {
                    $messages[] = $event->getMessage();
                }
            }

            $this->assertContains(
                $message,
                implode("\n", $messages)
            );
        });
    }

    public function testReportErrorGrpc()
    {
        $message = sprintf('Test Report Error gRPC (%s)', date('Y-m-d H:i:s'));
        $output = $this->runCommand('report-grpc', [
            'message' => $message,
            '--user' => 'unittests@google.com',
        ]);
        $this->assertEquals('Reported an exception to Stackdriver using gRPC' . PHP_EOL, $output);

        $errorStats = new ErrorStatsServiceClient();
        $projectName = $errorStats->formatProjectName(self::$projectId);
        $timeRange = (new QueryTimeRange())
            ->setPeriod(QueryTimeRange_Period::PERIOD_1_HOUR);

        // Iterate through all elements
        $this->runEventuallyConsistentTest(function () use (
            $errorStats,
            $projectName,
            $timeRange,
            $message
        ) {
            $messages = [];
            $response = $errorStats->listGroupStats($projectName, $timeRange);
            foreach ($response->iterateAllElements() as $groupStat) {
                $response = $errorStats->listEvents($projectName, $groupStat->getGroup()->getGroupId());
                foreach ($response->iterateAllElements() as $event) {
                    $messages[] = $event->getMessage();
                }
            }

            $this->assertContains(
                $message,
                implode("\n", $messages)
            );
        });
    }

    private function runCommand($commandName, $args = [])
    {
        $application = require __DIR__ . '/../error_reporting.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        try {
            $commandTester->execute(
                ['project_id' => self::$projectId] + $args,
                ['interactive' => false]);
        } catch (\Google\Cloud\ApiException $e) {
            // if the command throws an error cast it as a string (as this would be the output)
            $application->renderException($e, $commandTester->getOutput());
            return $commandTester->getDisplay();
        }
        return ob_get_clean();
    }
}
