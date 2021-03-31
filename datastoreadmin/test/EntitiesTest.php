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

class EntitiesTest extends TestCase
{
    use TestTrait {
        TestTrait::runFunctionSnippet as traitRunFunctionSnippet;
    }

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
    }

    public function testEntitiesLifecycle()
    {
        // TODO: get bucket for testing
        $uri = uniqid("gs://cloud-php-testdata/datastore-export-");

        $output = $this->runFunctionSnippet('entities_export', [
            'projectId' => self::$projectId,
            'outputUrlPrefix' => $uri,
        ]);

        $res = preg_match('/^The export operation succeeded\. File location is (gs:\/\/\S{0,})$/', $output, $matches);
        $this->assertEquals(
            1,
            $res,
            sprintf(
                "output message did not match expected.\nexpected: `%s`.\ngot: `%s`",
                "The export operation succeeded. File location is <uri>",
                $output
            )
        );

        $outputUri = $matches[1];

        $output = $this->runFunctionSnippet('entities_import', [
            'projectId' => self::$projectId,
            'inputUri' => $outputUri,
        ]);

        $this->assertEquals("The import operation succeeded", $output);
    }
}
