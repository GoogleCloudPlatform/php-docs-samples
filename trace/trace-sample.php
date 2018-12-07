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

require_once __DIR__ . '/vendor/autoload.php';

use OpenCensus\Trace\Exporter\NullExporter;
# [START trace_setup_php_use_statement]
use OpenCensus\Trace\Exporter\StackdriverExporter;
use OpenCensus\Trace\Tracer;

# [END trace_setup_php_use_statement]

$projectId = getenv('GOOGLE_PROJECT_ID');
if ($projectId === false) {
    die('Set GOOGLE_PROJECT_ID envvar');
}

# [START trace_setup_php_exporter_setup]
$exporter = new StackdriverExporter([
    'clientConfig' => [
        'projectId' => $projectId
    ]
]);
# [END trace_setup_php_exporter_setup]
// When running tests, use a null exporter instead.
if (getenv('USE_NULL_EXPORTER')) {
    $exporter = new NullExporter();
}
# [START trace_setup_php_tracer_start]
Tracer::start($exporter);
# [END trace_setup_php_tracer_start]

function trace_callable()
{
    # [START trace_setup_php_span_with_closure]
    Tracer::inSpan(
        ['name' => 'slow_function'],
        function () {
            sleep(1);
        }
    );
    # [END trace_setup_php_span_with_closure]
}
