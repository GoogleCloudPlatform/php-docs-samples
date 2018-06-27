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

use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\ErrorReporting\V1beta1\ErrorStatsServiceClient;
use Google\Cloud\ErrorReporting\V1beta1\QueryTimeRange;
use Google\Cloud\ErrorReporting\V1beta1\QueryTimeRange_Period;

trait VerifyReportedErrorTrait
{
    use EventuallyConsistentTestTrait;

    private function verifyReportedError($projectId, $message, $retryCount = 6)
    {
        $errorStats = new ErrorStatsServiceClient();
        $projectName = $errorStats->projectName($projectId);

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
            $response = $errorStats->listGroupStats($projectName, $timeRange, [
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

            $this->assertContains(
                $message,
                implode("\n", $messages)
            );
        }, $retryCount, true);
    }
}
