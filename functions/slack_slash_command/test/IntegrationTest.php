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

namespace Google\Cloud\Samples\Functions\SlackSlashCommand\Test;

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\CloudFunctionLocalTestTrait;

require_once __DIR__ . '/TestCasesTrait.php';

/**
 * Class IntegrationTest.
 */
class IntegrationTest extends TestCase
{
    use CloudFunctionLocalTestTrait;
    use TestCasesTrait;

    /**
     * Run the PHP server locally for the defined function.
     *
     * Overrides CloudFunctionLocalTestTrait::doRun().
     */
    private static function doRun()
    {
        self::$fn->run([
            'SLACK_SECRET' => self::$slackSecret,
            'KG_API_KEY' => self::$kgApiKey,
        ]);
    }

    /**
      * @dataProvider cases
      */
    public function testFunction(
        $label,
        $body,
        $method,
        $expected,
        $statusCode,
        $headers
    ): void {
        $response = $this->client->request(
            $method,
            '/',
            ['headers' => $headers, 'body' => $body]
        );
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $label . ': status code'
        );

        if ($expected !== null) {
            $output = (string) $response->getBody();
            $this->assertStringContainsString($expected, $output);
        }
    }
}
