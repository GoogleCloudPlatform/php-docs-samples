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

use Google\Cloud\TestUtils\TestTrait;

trait TestCasesTrait
{
    use TestTrait;

    private static $entryPoint = 'receiveRequest';
    private static $slackSecret;
    private static $kgApiKey;

    public static function getEnvVars()
    {
        self::$slackSecret = self::requireEnv('SLACK_SECRET');
        self::$kgApiKey = self::requireEnv('KG_API_KEY');
    }

    public static function cases(): array
    {
        self::getEnvVars();

        return [
            [
                'label' => 'Only allows POST',
                'body' => '',
                'method' => 'GET',
                'expected' => null,
                'statusCode' => '405',
                'headers' => self::validHeaders('')
            ],
            [
                'label' => 'Requires valid auth headers',
                'body' => 'text=foo',
                'method' => 'POST',
                'expected' => null,
                'statusCode' => '403',
                'headers' => [],
            ],
            [
                'label' => 'Doesn\'t allow blank body',
                'body' => '',
                'method' => 'POST',
                'expected' => null,
                'statusCode' => '400',
                'headers' => self::validHeaders(''),
            ],
            [
                'label' => 'Prohibits invalid signature',
                'body' => 'text=foo',
                'method' => 'POST',
                'expected' => null,
                'statusCode' => '403',
                'headers' => [
                    'X-Slack-Request-Timestamp' => '1',
                    'X-Slack-Signature' =>
                    'bad_signature'
                ],
            ],
            [
                'label' => 'Handles no-result query',
                'body' => 'text=asdfjkl13579',
                'method' => 'POST',
                'expected' => 'No results match your query',
                'statusCode' => '200',
                'headers' => self::validHeaders('text=asdfjkl13579'),
            ],
            [
                'label' => 'Handles query with results',
                'body' => 'text=lion',
                'method' => 'POST',
                'expected' => 'en.wikipedia.org',
                'statusCode' => '200',
                'headers' => self::validHeaders('text=lion'),
            ],
            [
                'label' => 'Ignores extra URL parameters',
                'body' => 'unused=foo&text=lion',
                'method' => 'POST',
                'expected' => 'en.wikipedia.org',
                'statusCode' => '200',
                'headers' => self::validHeaders('unused=foo&text=lion'),
            ],
        ];
    }

    private static function validHeaders($body): array
    {
        // Calculate test case signature
        $timestamp = date('U');
        $plaintext = sprintf('v0:%s:%s', $timestamp, $body);
        $hash = hash_hmac('sha256', $plaintext, self::$slackSecret);
        $signature = sprintf('v0=%s', $hash);

        // Return new test case
        return [
            'plaintext' => $plaintext,
            'X-Slack-Request-Timestamp' => $timestamp,
            'X-Slack-Signature' => $signature,
        ];
    }
}
