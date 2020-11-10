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

namespace Google\Cloud\Samples\Functions\HelloworldHttp\Test;

trait TestCasesTrait
{
    public static function cases(): array
    {
        return [
            [
                'label' => 'Default',
                'query' => [],
                'body' => [],
                'expected' => 'Hello, World!',
                'statusCode' => '200',
            ],
            [
                'label' => 'Querystring Sets Name',
                'query' => ['name' => 'Galaxy'],
                'body' => [],
                'expected' => 'Hello, Galaxy!',
                'statusCode' => '200',
            ],
            [
                'label' => 'Body Sets Name',
                'query' => [],
                'body' => ['name' => 'Universe'],
                'expected' => 'Hello, Universe!',
                'statusCode' => '200',
            ],
            [
                'label' => 'Querystring Overrides Body',
                'query' => ['name' => 'Priority'],
                'body' => ['name' => 'Overridden'],
                'expected' => 'Hello, Priority!',
                'statusCode' => '200',
            ],
            [
                'label' => 'HTML Escape',
                'query' => [],
                'body' => ['name' => '<script>script</script>'],
                'expected' => sprintf('Hello, %s!', '&lt;script&gt;script&lt;/script&gt;'),
                'statusCode' => '200',
            ],
        ];
    }
}
