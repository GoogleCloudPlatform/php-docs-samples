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
 * Class E2EDeploymentTrait
 * @package Google\Cloud\TestUtils
 *
 * Use this trait to deploy the project to GCP for an end-to-end test.
 */
trait E2EDeploymentTrait
{
    private static $gaeApp;
    private $client;

    public static $projectEnv = 'GOOGLE_PROJECT_ID';
    public static $versionEnv = 'GOOGLE_VERSION_ID';

    public static function setUpBeforeClass()
    {
        $project_id = getenv(self::$projectEnv);
        $e2e_test_version = getenv(self::$versionEnv);
        if ($project_id === false) {
            self::fail('Please set ' . self::$projectEnv . ' env var.');
        }
        if ($e2e_test_version === false) {
            self::fail('Please set ' . self::$versionEnv . ' env var.');
        }
        self::$gaeApp = new GaeApp(
            $project_id,
            $e2e_test_version
        );
        if (self::$gaeApp->deploy() === false) {
            self::fail('Deployment failed.');
        }
    }

    public static function tearDownAfterClass()
    {
        self::$gaeApp->delete();
    }

    /**
     * @before
     */
    public function setUpClient()
    {
        $url = self::$gaeApp->getBaseUrl();
        $this->client = new Client(['base_uri' => $url]);
    }
}
