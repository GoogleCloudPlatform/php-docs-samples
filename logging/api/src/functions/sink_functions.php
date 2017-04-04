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
// [START logging_use]
use Google\Cloud\Logging\LoggingClient;

// [END logging_use]

// [START create_sink]
/** Create a log sink.
 *
 * @param string $projectId The Google project ID.
 * @param string $sinkName The name of the sink.
 * @param string $destination The destination of the sink.
 * @param string $filterString The filter for the sink.
 */
function create_sink($projectId, $sinkName, $destination, $filterString)
{
    $logging = new LoggingClient(['projectId' => $projectId]);
    $logging->createSink(
        $sinkName,
        $destination,
        ['filter' => $filterString]
    );
}
// [END create_sink]

// [START delete_sink]
/** Delete a log sink.
 *
 * @param string $projectId The Google project ID.
 * @param string $sinkName The name of the sink.
 */
function delete_sink($projectId, $sinkName)
{
    $logging = new LoggingClient(['projectId' => $projectId]);
    $logging->sink($sinkName)->delete();
}
// [END delete_sink]

// [START list_sinks]
/**
 * List log sinks.
 *
 * @param string $projectId
 * @return ItemIterator<Google\Cloud\Logging\Sink>
 */
function list_sinks($projectId)
{
    $logging = new LoggingClient(['projectId' => $projectId]);
    return $logging->sinks();
}
// [END list_sinks]


// [START update_sink]
/**
 * Update a log sink.
 *
 * @param string $projectId
 * @param string sinkName
 * @param string $filterString
 */
function update_sink($projectId, $sinkName, $filterString)
{
    $logging = new LoggingClient(['projectId' => $projectId]);
    $sink = $logging->sink($sinkName);
    $sink->update(['filter' => $filterString]);
}
// [END update_sink]
