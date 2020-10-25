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

namespace Google\Cloud\Samples\Functions\HttpMethod\Test;

trait TestCasesTrait
{
    public static function cases(): array
    {
        return [
            [
                'method' => 'GET',
                'url' => '/',
                'status_code' => 200,
                'content' => 'Hello, World!'
            ],
            /*
             * TODO(ace-n):
             *
             * Uncomment when this PR is merged:
             *
             * https://github.com/GoogleCloudPlatform/php-tools/pull/89
             */
            /*[
                'method' => 'PUT',
                'url' => '/',
                'status_code' => 403,
                'content' => 'Forbidden!'
            ],
            [
                'method' => 'POST',
                'url' => '/',
                'status_code' => 405,
                'content' => 'something blew up!'
            ],*/
        ];
    }
}
