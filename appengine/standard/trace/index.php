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

use OpenCensus\Trace\Exporter\StackdriverExporter;
use OpenCensus\Trace\Tracer;

$exporter = new StackdriverExporter([
    'clientConfig' => [
        'projectId' => getenv('GOOGLE_CLOUD_PROJECT')
    ]
]);

Tracer::start($exporter);

function trace_callable()
{
    # [START span_with_closure]
    Tracer::inSpan(
        ['name' => 'slow_function'],
        function () {
            sleep(1);
        }
    );
    # [END span_with_closure]
    echo "Slow function called. This will be traced!\n";
}

trace_callable();
