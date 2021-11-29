<!--
Copyright 2020 Google LLC.
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
-->

<!-- [START functions_helloworld_http] ->
<?php

use Google\CloudFunctions\FunctionsFramework;
use Psr\Http\Message\ServerRequestInterface;

// Register the function with Functions Framework.
// This enables omitting the `FUNCTIONS_SIGNATURE_TYPE=http` environment
// variable when deploying. The `FUNCTION_TARGET` environment variable should
// match the first parameter.
FunctionsFramework::http('helloHttp', 'helloHttp');

function helloHttp(ServerRequestInterface $request): string
{
    $name = 'World';
    $body = $request->getBody()->getContents();
    if (!empty($body)) {
        $json = json_decode($body, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new RuntimeException(sprintf(
                'Could not parse body: %s',
                json_last_error_msg()
            ));
        }
        $name = $json['name'] ?? $name;
    }
    $queryString = $request->getQueryParams();
    $name = $queryString['name'] ?? $name;

    return sprintf('Hello, %s!', htmlspecialchars($name));
}

// [END functions_helloworld_http]
