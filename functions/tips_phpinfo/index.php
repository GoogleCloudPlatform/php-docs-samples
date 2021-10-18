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

// [START functions_tips_phpinfo]

use Psr\Http\Message\ServerRequestInterface;

function phpInfoDemo(ServerRequestInterface $request): string
{
    // phpinfo() displays its output directly in the function's
    // HTTP response, so we don't need to explicitly return it
    //
    // Note: we recommend deleting the deployed Cloud Function once you no
    // longer need it, as phpinfo() may broadcast potential security issues.
    phpinfo();
    return '';
}

// [END functions_tips_phpinfo]
