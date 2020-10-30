<?php
/**
 * Copyright 2020 Google LLC.
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

function _heavyComputation(): int
{
    return 1 * 2 * 3 * 4 * 5;
}

function _lightComputation(): int
{
    return 1 + 2 + 3 + 4 + 5;
}

// [START functions_tips_scopes]

use Psr\Http\Message\ServerRequestInterface;

function scopeDemo(ServerRequestInterface $request): string
{
    // Heavy computations should be cached between invocations.
    // The PHP runtime does NOT preserve variables between invocations, so we
    // must write their values to a file.
    // (All writable directories in Cloud Functions are in-memory, so these
    // operations are typically fast.)
    $cachePath = sys_get_temp_dir() . 'cached_value.txt';

    if (file_exists($cachePath)) {
        // Read cached value from file
        $instanceVar = file_get_contents($cachePath);
    } else {
        // Compute cached value + write to file
        $instanceVar = _heavyComputation();
        file_put_contents($cachePath, $instanceVar);
    }

    // Lighter computations can re-run on each function invocation.
    $functionVar = _lightComputation();

    $response = 'Per instance: ' . $instanceVar . PHP_EOL;
    $response .= 'Per function: ' . $functionVar . PHP_EOL;

    return $response;
}

// [END functions_tips_scopes]
