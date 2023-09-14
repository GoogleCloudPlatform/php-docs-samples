<?php

/**
 * Copyright 2023 Google LLC.
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

// [START functions_typed_greeting]

use Google\CloudFunctions\FunctionsFramework;

class GreetingRequest
{
    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    public function __construct(string $first_name = '', string $last_name = '')
    {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
    }

    public function serializeToJsonString(): string
    {
        return json_encode([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
        ]);
    }

    public function mergeFromJsonString(string $body): void
    {
        $obj = json_decode($body);
        $this->first_name = $obj['first_name'];
        $this->last_name = $obj['last_name'];
    }
}

class GreetingResponse
{
    /** @var string */
    public $message;

    public function __construct(string $message = '')
    {
        $this->message = $message;
    }

    public function serializeToJsonString(): string
    {
        return json_encode([
            'message' => $message,
        ]);
    }

    public function mergeFromJsonString(string $body): void
    {
        $obj = json_decode($body);
        $this->message = $obj['message'];
    }
};

function greeting(GreetingRequest $req): GreetingResponse
{
    return new GreetingResponse("Hello $req->first_name $req->last_name!");
};

FunctionsFramework::typed('greeting', 'greeting');

// [END functions_typed_greeting]
