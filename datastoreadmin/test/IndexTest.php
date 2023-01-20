<?php
/**
 * Copyright 2021 Google LLC
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

namespace Google\Cloud\Samples\DatastoreAdmin;

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    use TestTrait;

    public function testIndexLifecycle()
    {
        $kind = uniqid('php-docs-samples-index-kind-');
        $output = $this->runFunctionSnippet('index_create', [
            'projectId' => self::$projectId,
            'kind' => $kind,
            // 2 minutes required for these tests to pass
            'initialPollDelayMillis' => 120000
        ]);

        $res = preg_match('/^The create index operation succeeded\. Index ID: (\S{0,})\n$/', $output, $matches);
        $this->assertEquals(
            1,
            $res,
            sprintf(
                "output message did not match expected.\nexpected: `%s`.\ngot: `%s`",
                'The create index operation succeeded. Index ID: <id>',
                $output
            )
        );

        $indexId = $matches[1];

        $output = $this->runFunctionSnippet('index_get', [
            'projectId' => self::$projectId,
            'indexId' => $indexId,
        ]);

        $this->assertEquals(
            sprintf("Index ID: %s\n", $indexId),
            $output
        );

        $output = $this->runFunctionSnippet('index_list', [
            'projectId' => self::$projectId,
        ]);

        $this->assertStringContainsString(sprintf('Index ID: %s' . PHP_EOL, $indexId), $output);

        $output = $this->runFunctionSnippet('index_delete', [
            'projectId' => self::$projectId,
            'indexId' => $indexId,
        ]);

        $this->assertEquals('The delete index operation succeeded.' . PHP_EOL, $output);
    }
}
