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

namespace Google\Cloud\Samples\Functions\HttpContentType\Test;

trait TestCasesTrait
{
    public static function cases(): array
    {
        return [
            [
                'content-type' => 'text/plain',
                'body' => 'John',
                'code' => '200',
                'expected' => 'Hello John!',
            ],
            [
                'content-type' => 'application/json',
                'body' => json_encode(['name' => 'John']),
                'code' => '200',
                'expected' => 'Hello John!',
            ],
            [
                'content-type' => 'application/octet-stream',
                'body' => 'John',
                'code' => '200',
                'expected' => 'Hello John!',
            ],
            [
                'content-type' => 'application/x-www-form-urlencoded',
                'body' => 'name=John',
                'code' => '200',
                'expected' => 'Hello John!',
            ],
        ];
    }
}
