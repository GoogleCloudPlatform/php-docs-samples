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

namespace Google\Cloud\Samples\Logging;

// [START logging_list_log_entries]
use Google\Cloud\Logging\LoggingClient;

/**
 * Print the timestamp and entry for the project and logger.
 *
 * @param string $projectId The Google project ID.
 * @param string $loggerName The name of the logger.
 */
function list_entries($projectId, $loggerName)
{
    $logging = new LoggingClient(['projectId' => $projectId]);
    $loggerFullName = sprintf('projects/%s/logs/%s', $projectId, $loggerName);
    $oneDayAgo = date(\DateTime::RFC3339, strtotime('-24 hours'));
    $filter = sprintf(
        'logName = "%s" AND timestamp >= "%s"',
        $loggerFullName,
        $oneDayAgo
    );
    $options = [
        'filter' => $filter,
    ];
    $entries = $logging->entries($options);

    // Print the entries
    foreach ($entries as $entry) {
        /* @var $entry \Google\Cloud\Logging\Entry */
        $entryInfo = $entry->info();
        if (isset($entryInfo['textPayload'])) {
            $entryText = $entryInfo['textPayload'];
        } else {
            $entryPayload = [];
            foreach ($entryInfo['jsonPayload'] as $key => $value) {
                $entryPayload[] = "$key: $value";
            }
            $entryText = '{' . implode(', ', $entryPayload) . '}';
        }
        printf('%s : %s' . PHP_EOL, $entryInfo['timestamp'], $entryText);
    }
}
// [END logging_list_log_entries]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
