<?php
/**
 * Copyright 2016 Google Inc.
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
namespace Google\Cloud\Test;

use GuzzleHttp\Client;
use Google\Cloud\TestUtils\GaeApp;

class LocalTest extends \PHPUnit_Framework_TestCase
{
    private static $gaeApp;

    private $client;

    const PROJECT_ENV = 'GOOGLE_PROJECT_ID';
    const VERSION_ENV = 'GOOGLE_VERSION_ID';

    private static function getVersion()
    {
        return "modules-" . getenv(self::VERSION_ENV);
    }

    private static function getTargetDir()
    {
        return realpath(__DIR__ . '/../');
    }

    public static function setUpBeforeClass()
    {
        self::markTestIncomplete(
            'dev_appserver.py is not working for us now');
        $project_id = getenv(self::PROJECT_ENV);
        $e2e_test_version = getenv(self::VERSION_ENV);
        if ($project_id === false) {
            self::fail('Please set ' . self::PROJECT_ENV . ' env var.');
        }
        if ($e2e_test_version === false) {
            self::fail('Please set ' . self::VERSION_ENV . ' env var.');
        }
        self::$gaeApp = new GaeApp(
            $project_id,
            $e2e_test_version,
            self::getTargetDir());
        if (self::$gaeApp->run() === false) {
            self::fail('dev_appserver failed.');
        }
    }

    public static function tearDownAfterClass()
    {
        self::$gaeApp->stop();
    }

    public function setUp()
    {
        $url = self::$gaeApp->getLocalBaseUrl();
        $this->client = new Client(['base_uri' => $url]);
    }

    public function testIndex()
    {
        // Access the modules app top page.
        $resp = $this->client->get('');
        $this->assertEquals('200', $resp->getStatusCode(),
                            'top page status code');
        $this->assertContains(
            'default:',
            $resp->getBody()->getContents());
    }
}
