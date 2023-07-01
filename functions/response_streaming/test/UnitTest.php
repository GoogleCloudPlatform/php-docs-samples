<?php
/**
 * Copyright 2023 Google Inc.
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

/**
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/dlp/README.md
 */
declare(strict_types=1);

namespace Google\Cloud\Samples\Functions\ResponseStreaming\Test;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Cloud Function.
 */
class UnitTest extends TestCase
{
    private static $entryPoint = 'streamBigQuery';

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../index.php';
    }

    public function testFunction(): void
    {
        $request = new ServerRequest('POST', '/', [], '');
        ob_start();
        $this->runFunction(self::$entryPoint, [$request]);
        $result = ob_get_clean();
        $this->assertStringContainsString('Successfully streamed rows', $result);
    }

    private static function runFunction($functionName, array $params = []): void
    {
        call_user_func_array($functionName, $params);
    }
}
