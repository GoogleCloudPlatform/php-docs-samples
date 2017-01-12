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

use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class quickstartTest extends PHPUnit_Framework_TestCase
{
    public function testQuickstart()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('GOOGLE_PROJECT_ID must be set.');
        }

        $version = 'quickstart-tests-' . time();
        $file = sys_get_temp_dir() . '/error_reporting_quickstart.php';
        $contents = file_get_contents(__DIR__ . '/../quickstart.php');
        $contents = str_replace(
            ['YOUR_PROJECT_ID', '1.0-dev', '__DIR__'],
            [$projectId, $version, sprintf('"%s/.."', __DIR__)],
            $contents
        );
        file_put_contents($file, $contents);

        // Invoke quickstart.php
        ob_start();
        passthru(sprintf('php %s', $file));
        $output = ob_get_clean();

        // Make sure it looks correct
        $this->assertEquals(
            'Exception logged to Stack Driver Error Reporting' . PHP_EOL,
            $output
        );

        // Ensure the log actually showed up
        sleep(1);

        // create an authorized Google Client
        $middleware = ApplicationDefaultCredentials::getMiddleware(
            'https://www.googleapis.com/auth/cloud-platform'
        );
        $stack = HandlerStack::create();
        $stack->push($middleware);
        $baseUrl = 'https://clouderrorreporting.googleapis.com/v1beta1/projects/';
        $client = new Client([
            'handler' => $stack,
            'base_uri' => $baseUrl,
            'auth' => 'google_auth', // authorize all requests
            'query' => [
                'serviceFilter.version' => $version,
            ]
        ]);

        // call groupStats to get the latest logs per version
        $url = sprintf('%s/groupStats', $projectId);
        $res = $client->get($url);
        $response = json_decode((string) $res->getBody(), true);

        //
        $this->assertArrayHasKey('errorGroupStats', $response);
        $this->assertEquals(1, count($response['errorGroupStats']));
        $this->assertArrayHasKey(
            'representative',
            $response['errorGroupStats'][0]
        );
        $this->assertContains(
            'This will be logged to Stack Driver Error Reporting',
            $response['errorGroupStats'][0]['representative']['message']
        );
    }
}
