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

// Global (instance-wide) scope
// This computation runs at instance cold-start
$instanceVar = _heavyComputation();

function scopeDemo(ServerRequestInterface $request): string
{
    global $instanceVar;

    // Per-function scope
    // This computation runs every time this function is called
    $functionVar = _lightComputation();

    $response = 'Per instance: ' . $instanceVar . PHP_EOL;
    $response .= 'Per function: ' . $functionVar . PHP_EOL;

    return $response;
}

// [END functions_tips_scopes]
