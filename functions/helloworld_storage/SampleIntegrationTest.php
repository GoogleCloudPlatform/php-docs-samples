<?php
/**
 * Copyright 2020 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

// [START functions_storage_integration_test]

namespace Google\Cloud\Samples\Functions\HelloworldStorage;

use Google\CloudFunctions\CloudEvent;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Class SampleIntegrationTest.
 *
 * Integration tests for the 'Helloworld Storage' Cloud Function.
 */
class SampleIntegrationTest extends TestCase
{
    /** @var Process */
    private static $process;

    /** @var Client */
    private static $client;

    public function dataProvider()
    {
        return [
            [
                'cloudevent' => [
                    'id' => uniqid(),
                    'source' => 'storage.googleapis.com',
                    'specversion' => '1.0',
                    'type' => 'google.cloud.storage.object.v1.finalized',
                ],
                'data' => [
                    'bucket' => 'some-bucket',
                    'metageneration' => '1',
                    'name' => 'folder/friendly.txt',
                    'timeCreated' => '2020-04-23T07:38:57.230Z',
                    'updated' => '2020-04-23T07:38:57.230Z',
                ],
                'statusCode' => '200',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHelloGCS(array $cloudevent, array $data, string $statusCode): void
    {
        // Prepare the HTTP headers for a CloudEvent.
        $cloudEventHeaders = [];
        foreach ($cloudevent as $key => $value) {
            $cloudEventHeaders['ce-' . $key] = $value;
        }

        // Send an HTTP request using CloudEvent metadata.
        $resp = self::$client->post('/', [
            'body' => json_encode($data),
            'headers' => $cloudEventHeaders + [
                // Instruct the function framework to parse the body as JSON.
                'content-type' => 'application/json'
            ],
        ]);

        // The Cloud Function logs all data to stderr.
        $actual = self::$process->getIncrementalErrorOutput();

        // Confirm the status code.
        $this->assertEquals($statusCode, $resp->getStatusCode());

        // Verify the CloudEvent and data properties are logged by the function.
        foreach ($data as $property => $value) {
            $this->assertStringContainsString($value, $actual);
        }
        $this->assertStringContainsString($cloudevent['id'], $actual);
        $this->assertStringContainsString($cloudevent['type'], $actual);
    }

    /**
     * Start a local PHP server running the Functions Framework.
     *
     * @beforeClass
     */
    public static function startFunctionFramework(): void
    {
        $port = getenv('PORT') ?: '8080';
        $php = (new PhpExecutableFinder())->find();
        $uri = 'localhost:' . $port;

        // https://symfony.com/doc/current/components/process.html#usage
        self::$process = new Process([$php, '-S', $uri, 'vendor/bin/router.php'], null, [
            'FUNCTION_SIGNATURE_TYPE' => 'cloudevent',
            'FUNCTION_TARGET' => 'helloGCS',
        ]);
        self::$process->start();

        // Initialize an HTTP client to drive requests.
        self::$client = new Client(['base_uri' => 'http://' . $uri]);

        // Short delay to ensure PHP server is ready.
        sleep(1);
    }

    /**
     * Stop the local PHP server.
     *
     * @afterClass
     */
    public static function stopFunctionFramework(): void
    {
        if (!self::$process->isRunning()) {
            echo self::$process->getErrorOutput();
            throw new RuntimeException('Function Framework PHP process not running by end of test');
        }
        self::$process->stop(3, SIGTERM);
    }
}

// [END functions_storage_integration_test]
