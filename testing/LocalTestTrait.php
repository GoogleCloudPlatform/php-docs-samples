<?php
/*
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\TestUtils;

use GuzzleHttp\Client;

/**
 * Class LocalTestTrait
 * @package Google\Cloud\TestUtils
 *
 * Use this trait to use dev_appserver for testing.
 */
trait LocalTestTrait
{
    private static $gaeApp;
    private $client;

    /**
     * @beforeClass
     */
    public static function startServer()
    {
        self::markTestIncomplete(
            'dev_appserver.py is not working for us now.'
            . ' TODO: Make this work with specifying --php_gae_extension_path'
        );
        self::$gaeApp = new GaeApp('', '');
        if (self::$gaeApp->run() === false) {
            self::fail('dev_appserver failed');
        }
    }

    /**
     * @afterClass
     */
    public static function stopServer()
    {
        self::$gaeApp->stop();
    }

    /**
     * @before
     */
    public function setUpClient()
    {
        $url = self::$gaeApp->getLocalBaseUrl();
        $this->client = new Client(['base_uri' => $url]);
    }
}
