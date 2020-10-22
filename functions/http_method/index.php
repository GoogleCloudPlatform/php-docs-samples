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

use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Response;

function httpMethod(ServerRequestInterface $request): Response
{
    switch ($request->getMethod()) {
        case 'GET':
            return new Response(
                200,
                [],
                'Hello, World!' . PHP_EOL
            );
            break;
        case 'PUT':
            return new Response(
                403,
                [],
                'Forbidden!' . PHP_EOL
            );
            break;
        default:
            $json_payload = json_encode([
                'error' => 'something blew up!'
            ]);
            return new Response(
                405,
                ['Content-Type' => 'application/json'],
                $json_payload
            );
            break;
    }
}

// [END functions_http_method]
