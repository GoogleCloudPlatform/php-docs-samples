<?php
/**
 * Copyright 2018 Google Inc.
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
namespace Google\Cloud\Test\ErrorReporting;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\ErrorReporting\V1beta1\ErrorStatsServiceClient;
use Google\Cloud\ErrorReporting\V1beta1\QueryTimeRange;
use Google\Cloud\ErrorReporting\V1beta1\QueryTimeRange_Period;

use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;
    use EventuallyConsistentTestTrait;

    public function setup()
    {
        $this->projectId = getenv('GOOGLE_PROJECT_ID');
    }

    public function testIndex()
    {
        // Access the modules app top page.
        $response = $this->client->get('');
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertContains(
            'Click an error type',
            $response->getBody()->getContents()
        );
    }

    public function testExceptions()
    {
        // Access the modules app top page.
        $response = $this->client->get('', [
            'query' => ['type' => 'exception']
        ]);

        $this->assertEquals('200', $response->getStatusCode());
        $this->assertContains(
            'Throwing a PHP Exception.',
            $response->getBody()->getContents()
        );

        $this->verifyReportedError('This is from "throw new Exception()"');
    }

    public function testUserErrors()
    {
        // Access the modules app top page.
        $response = $this->client->get('', [
            'query' => ['type' => 'error']
        ]);

        $this->assertEquals('200', $response->getStatusCode());
        $this->assertContains(
            'Triggering a PHP Error.',
            $response->getBody()->getContents()
        );

        $this->verifyReportedError('This is from "trigger_error()"');
    }

    public function testFatalErrors()
    {
        // Access the modules app top page.
        $response = $this->client->get('', [
            'query' => ['type' => 'fatal']
        ]);

        $this->assertEquals('200', $response->getStatusCode());
        $this->assertContains(
            'Triggering a PHP Fatal Error by including a file with a syntax error.',
            $response->getBody()->getContents()
        );

        $this->verifyReportedError('ParseError: syntax error, unexpected end of file');
    }

    private function verifyReportedError($message, $retryCount = 5)
    {
        $errorStats = new ErrorStatsServiceClient();
        $projectName = $errorStats->projectName($this->projectId);

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
                $response = $errorStats->listEvents($projectName, $groupStat->getGroup()->getGroupId(), [
                    'timeRange' => $timeRange,
                ]);
                foreach ($response->iterateAllElements() as $event) {
                    $messages[] = $event->getMessage();
                }
            }

            $this->assertContains(
                $message,
                implode("\n", $messages)
            );
        }, $retryCount, true);
    }
}
