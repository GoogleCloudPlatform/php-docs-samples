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

// [START logging_update_sink]
use Google\Cloud\Logging\LoggingClient;

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
    printf("Updated a sink '%s'." . PHP_EOL, $sinkName);
}
// [END logging_update_sink]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
