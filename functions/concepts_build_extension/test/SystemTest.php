<?php
/**
 * Copyright 2021 Google LLC.
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

namespace Google\Cloud\Samples\Functions\ConceptsBuildExtension\Test;

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\CloudFunctionLocalTestTrait;
use Google\Cloud\TestUtils\GcloudWrapper\CloudFunction;
use Google\Cloud\Utils\ExponentialBackoff;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Class SystemTest.
 */
class SystemTest extends TestCase
{
    use CloudFunctionLocalTestTrait;

    private static $entryPoint = 'helloBuildExtension';

    public function testFunction(): void
    {
        // Send a request to the function.
        $resp = $this->client->get('/');

        // Assert status code.
        $this->assertEquals('200', $resp->getStatusCode());

        // Assert function output.
        $output = trim((string) $resp->getBody());
        $this->assertEquals(
            'Hello World! (from my_custom_extension.so)',
            $output
        );
    }

    /**
     * Start the development server based on the prepared function.
     *
     * We override this to run the "gcp-build" step and to enable the built
     * extension when running the PHP build-in-web server.
     */
    private static function doRun()
    {
        // Build the extension
        $process = new Process(['composer', 'run-script', 'gcp-build']);
        $process->start();

        $backoff = new ExponentialBackoff($retries = 10);
        $backoff->execute(function () use ($process) {
            if ($process->isRunning()) {
                throw new \Exception('waiting for "gcp-build" step to complete');
            }
        });

        $phpBin = (new PhpExecutableFinder())->find();
        $phpBin .= ' -d extension=\'./my_custom_extension.so\'';
        return self::$fn->run([], CloudFunction::DEFAULT_PORT, $phpBin);
    }
}
