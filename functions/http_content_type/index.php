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

// [START functions_http_content]

use Psr\Http\Message\ServerRequestInterface;

function helloContent(ServerRequestInterface $request): string
{
    $name = 'World';
    $body = $request->getBody()->getContents();
    switch ($request->getHeaderLine('content-type')) {
        // '{"name":"John"}'
        case 'application/json':
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
          break;
        // 'John', stored in a stream
        case 'application/octet-stream':
          $name = $body;
          break;
        // 'John'
        case 'text/plain':
          $name = $body;
          break;
        // 'name=John' in the body of a POST request (not the URL)
        case 'application/x-www-form-urlencoded':
          parse_str($body, $data);
          $name = $data['name'] ?? $name;
          break;
    }

    return sprintf('Hello %s!', htmlspecialchars($name));
}

// [END functions_http_content]
