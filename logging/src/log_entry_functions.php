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

use Google\Cloud\Core\Iterator\ItemIterator;
// [START logging_write_log_entry]
// [START logging_list_log_entries]
// [START logging_delete_log]
use Google\Cloud\Logging\LoggingClient;

// [END logging_write_log_entry]
// [END logging_list_log_entries]
// [END logging_delete_log]

// [START logging_write_log_entry]
/** Write a log message via the Stackdriver Logging API.
 *
 * @param string $projectId The Google project ID.
 * @param string $loggerName The name of the logger.
 * @param string $message The log message.
 */
function write_log($projectId, $loggerName, $message)
{
    $logging = new LoggingClient(['projectId' => $projectId]);
    $logger = $logging->logger($loggerName, [
        'resource' => [
            'type' => 'gcs_bucket',
            'labels' => [
                'bucket_name' => 'my_bucket'
            ]
        ]
    ]);
    $entry = $logger->entry($message);
    $logger->write($entry);
    printf("Wrote a log to a logger '%s'." . PHP_EOL, $loggerName);
}
// [END logging_write_log_entry]

// [START logging_list_log_entries]
/** Return an iterator for listing log entries.
 *
 * @param string $projectId The Google project ID.
 * @param string $loggerName The name of the logger.
 * @return ItemIterator<Google\Cloud\Logging\Entry>
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
        printf("%s : %s" . PHP_EOL, $entryInfo['timestamp'], $entryText);
    }
}
// [END logging_list_log_entries]

// [START logging_delete_log]
/** Delete a logger and all its entries.
 *
 * @param string $projectId The Google project ID.
 * @param string $loggerName The name of the logger.
 */
function delete_logger($projectId, $loggerName)
{
    $logging = new LoggingClient(['projectId' => $projectId]);
    $logger = $logging->logger($loggerName);
    $logger->delete();
    printf("Deleted a logger '%s'." . PHP_EOL, $loggerName);
}
// [END logging_delete_log]
