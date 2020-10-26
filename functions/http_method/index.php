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

// [START functions_http_method]

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Response;

function httpMethod(ServerRequestInterface $request): ResponseInterface
{
    switch ($request->getMethod()) {
        case 'GET':
            // Example: read request
            return new Response(
                200, // OK
                [],
                'Hello, World!' . PHP_EOL
            );
            break;
        case 'PUT':
            // Example: write request to a read-only resource
            return new Response(
                403, // Permission denied
                [],
                'Forbidden!' . PHP_EOL
            );
            break;
        default:
            // Example: request type not supported by the application
            $json_payload = json_encode([
                'error' => 'something blew up!'
            ]);
            return new Response(
                405, // Method not allowed
                ['Content-Type' => 'application/json'],
                $json_payload
            );
            break;
    }
}

// [END functions_http_method]
