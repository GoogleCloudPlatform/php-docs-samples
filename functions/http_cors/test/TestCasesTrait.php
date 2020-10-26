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
declare(strict_types=1);

namespace Google\Cloud\Samples\Functions\HttpCors\Test;

trait TestCasesTrait
{
    public static function cases(): array
    {
        return [
            [
                'url' => '/',
                'method' => 'OPTIONS',
                'status_code' => 204,
                'contains_header' => 'Access-Control-Max-Age',
                'not_contains_header' => null,
                'contains_content' => null,
                'not_contains_content' => 'Hello World!'
            ],
            [
                'url' => '/',
                'method' => 'GET',
                'status_code' => 200,
                'contains_header' => 'Access-Control-Allow-Origin',
                'contains_header' => null,
                'not_contains_header' => 'Access-Control-Max-Age',
                'contains_content' => 'Hello World!',
                'not_contains_content' => null,
            ],
        ];
    }
}
