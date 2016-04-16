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

    private static $projectEnv = 'GOOGLE_PROJECT_ID';
    private static $versionEnv = 'GOOGLE_VERSION_ID';
    private static $projectId;
    private static $versionId;

    /**
     * @beforeClass
     */
    public static function deployApp()
    {
        if (getenv('RUN_DEPLOYMENT_TESTS') !== 'true') {
            self::markTestSkipped(
                'To run this test, set RUN_DEPLOYMENT_TESTS env to true.'
            );
        }
        self::$projectId = getenv(self::$projectEnv);
        self::$versionId = getenv(self::$versionEnv);
        if (self::$projectId === false) {
            self::fail('Please set ' . self::$projectEnv . ' env var.');
        }
        if (self::$versionId === false) {
            self::fail('Please set ' . self::$versionEnv . ' env var.');
        }
        self::$gaeApp = new GaeApp(
            self::$projectId,
            self::$versionId
        );
        if (self::$gaeApp->deploy() === false) {
            self::fail('Deployment failed.');
        }
    }

    /**
     * @afterClass
     */
    public static function deleteApp()
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
