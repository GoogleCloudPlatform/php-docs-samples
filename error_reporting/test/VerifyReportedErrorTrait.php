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

namespace Google\Cloud\Samples\ErrorReporting;

use Google\ApiCore\ApiException;
use Google\Cloud\Core\ExponentialBackoff;
use Google\Cloud\ErrorReporting\V1beta1\ErrorStatsServiceClient;
use Google\Cloud\ErrorReporting\V1beta1\QueryTimeRange;
use Google\Cloud\ErrorReporting\V1beta1\QueryTimeRange\Period;
use Google\Cloud\TestUtils\TestTrait;
use Google\Rpc\Code;
use PHPUnit\Framework\ExpectationFailedException;

trait VerifyReportedErrorTrait
{
    use TestTrait;

    private function verifyReportedError($projectId, $message)
    {
        $retries = 20; // Retry for 20 minutes
        $backoff = new ExponentialBackoff($retries, function ($exception) {
            // retry if the exception is resource exhausted from Google APIs
            if ($exception instanceof ApiException
                && $exception->getCode() == Code::RESOURCE_EXHAUSTED) {
                return true;
            }

            // retry if the exxception is PHPUnit failed assertion
            if ($exception instanceof ExpectationFailedException
                || $exception instanceof \PHPUnit_Framework_ExpectationFailedException) {
                return true;
            }
        });

        $errorStats = new ErrorStatsServiceClient();
        $projectName = $errorStats->projectName($projectId);

        $timeRange = (new QueryTimeRange())
            ->setPeriod(Period::PERIOD_1_HOUR);

        // Iterate through all elements
        $testFunc = function () use ($errorStats, $projectName, $timeRange, $message) {
            $messages = [];
            $response = $errorStats->listGroupStats($projectName, [
                'timeRange' => $timeRange,
                'pageSize' => 100,
            ]);
            foreach ($response->iterateAllElements() as $groupStat) {
                $response = $errorStats->listEvents($projectName, $groupStat->getGroup()->getGroupId(), [
                    'timeRange' => $timeRange,
                    'pageSize' => 100,
                ]);
                foreach ($response->iterateAllElements() as $event) {
                    $messages[] = $event->getMessage();
                }
            }

            $this->assertContains($message, implode("\n", $messages));
        };

        $backoff->execute($testFunc);
    }
}
