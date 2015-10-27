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

// @TODO: use test bootstrap
require_once __DIR__ . '/../vendor/autoload.php';

use Silex\WebTestCase;

class pubsubTest extends WebTestCase
{
    public function createApplication()
    {
        $appCode = file_get_contents(__DIR__.'/../web/index.php');

        // instead of running it, return it
        $appCode = str_replace('$app->run();', 'return $app;', $appCode);
        // remove opening PHP tag - eval doesn't like this
        $appCode = str_replace('<?php', '', $appCode);

        // pull the app and evaluate it
        $app = eval($appCode);
        $app['session.test'] = true;
        $app['debug'] = true;
        $config = $app['config'];
        $config['project'] = 'cloud-samples-tests-php';
        $app['config'] = $config;

        // this will be set by travis, but may not be set locally
        if (!$credentials = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $credentials = __DIR__.'/../../credentials.json';
            putenv('GOOGLE_APPLICATION_CREDENTIALS='.$credentials);
        }

        if (!file_exists($credentials) || 0 == filesize($credentials)) {
            $this->markTestSkipped('credentials not found');
        }

        // prevent HTML error exceptions
        unset($app['exception_handler']);

        return $app;
    }

    public function testInitialPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
    }
}