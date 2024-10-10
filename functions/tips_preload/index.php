<?php
/**
 * Copyright 2021 Google LLC.
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

// [START functions_tips_preload]

use Psr\Http\Message\ServerRequestInterface;
use Google\Cloud\Samples\Functions\TipsPreload\ClassToPreload;

function preloadDemo(ServerRequestInterface $request = null): string
{
    // Verify the class exists without making a call to the autoloader
    $classIsPreloaded = class_exists(ClassToPreload::class, $autoload = false);

    return sprintf(
        'Class is preloaded: %s',
        var_export($classIsPreloaded, true)
    );
}
// [END functions_tips_preload]
