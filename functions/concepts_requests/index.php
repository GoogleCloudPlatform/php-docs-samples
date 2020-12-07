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

// [START functions_concepts_requests]

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

function makeRequest(ServerRequestInterface $request): ResponseInterface
{
    // This sample uses the GuzzleHTTP client
    // See its documentation for usage information
    // https://docs.guzzlephp.org/en/stable/

    // Specify the URL to send requests to
    $client = new Client([
        'base_uri' => 'https://example.com',
    ]);

    // Send the request
    $url_response = $client->get('/');

    $function_response = new Response(
        $url_response->getStatusCode(),
        [], // headers
        ''  // body
    );

    return $function_response;
}

// [END functions_concepts_requests]
