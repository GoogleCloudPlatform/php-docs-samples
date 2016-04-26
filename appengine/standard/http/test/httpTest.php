<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
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
use Silex\WebTestCase;

class httpTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../app.php';

        // set some parameters for testing
        $app['session.test'] = true;
        $app['debug'] = true;

        // prevent HTML error exceptions
        unset($app['exception_handler']);

        return $app;
    }

    public function testHome()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testHttpRequest()
    {
        $client = $this->createClient();

        $crawler = $client->request('POST', '/http_request');

        $this->assertTrue($client->getResponse()->isOk());
        $result = <<<EOF
{
  "args": {
    "query": "update"
  },
  "data": "",
  "files": {},
  "form": {
    "data": "this",
    "data2": "that"
  },
  "headers": {
    "Accept": "*/*",
    "Content-Length": "20",
    "Content-Type": "application/x-www-form-urlencoded",
    "Custom-Header": "custom-value",
    "Custom-Header-Two": "custom-value-2",
    "Host": "httpbin.org"
  },
  "json": null,
  "origin": "ORIGIN_IP_ADDRESS",
  "url": "http://httpbin.org/post?query=update"
}
EOF;
        // fix issue where there are trailing spaces after the commas
        $result = str_replace(',', ', ', $result);

        // escape for regex
        $result = '#' . preg_quote(htmlspecialchars($result), '#') . '#';

        // allow for any IP address
        $result = str_replace('ORIGIN_IP_ADDRESS', '.*', $result);

        // test the regex exists
        $this->assertRegExp($result, (string) $client->getResponse());
    }
}
