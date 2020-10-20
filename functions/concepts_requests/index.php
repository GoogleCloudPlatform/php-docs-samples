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
use GuzzleHttp\Psr7\Response;

function makeRequest(ServerRequestInterface $request): Response
{
    $curl = new Curl\Curl();

    // URL to send the request to
    $url = 'https://example.com';

    $curl->get($url);

    $status = $curl->error ? 500 : 200;
    $response = new Response($status, [], '');

    return $response;
}

// [END functions_concepts_requests]
