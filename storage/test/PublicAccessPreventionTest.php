<?php
/**
 * Copyright 2021 Google LLC
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

namespace Google\Cloud\Samples\Storage\Tests;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for public access prevention
 */
class PublicAccessPreventionTest extends TestCase
{
    use TestTrait;

    private static $storage;
    private static $bucket;

    public static function setUpBeforeClass(): void
    {
        self::$storage = new StorageClient();
        self::$bucket = self::$storage->createBucket(
            uniqid('samples-public-access-prevention-')
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::$bucket->delete();
    }

    public function testSetPublicAccessPreventionToEnforced()
    {
        $output = self::runFunctionSnippet('set_public_access_prevention_enforced', [
            self::$bucket->name(),
        ]);

        $this->assertStringContainsString(
            sprintf(
                'Public Access Prevention has been set to enforced for %s.',
                self::$bucket->name()
            ),
            $output
        );

        self::$bucket->reload();
        $bucketInformation = self::$bucket->info();
        $pap = $bucketInformation['iamConfiguration']['publicAccessPrevention'];
        $this->assertEquals('enforced', $pap);
    }

    /** @depends testSetPublicAccessPreventionToEnforced */
    public function testSetPublicAccessPreventionToInherited()
    {
        $output = self::runFunctionSnippet('set_public_access_prevention_inherited', [
            self::$bucket->name(),
        ]);

        $this->assertStringContainsString(
            sprintf(
                'Public Access Prevention has been set to inherited for %s.',
                self::$bucket->name()
            ),
            $output
        );

        self::$bucket->reload();
        $bucketInformation = self::$bucket->info();
        $pap = $bucketInformation['iamConfiguration']['publicAccessPrevention'];
        $this->assertEquals('inherited', $pap);
    }

    /** @depends testSetPublicAccessPreventionToInherited */
    public function testGetPublicAccessPrevention()
    {
        $output = self::runFunctionSnippet('get_public_access_prevention', [
            self::$bucket->name(),
        ]);

        $this->assertStringContainsString(
            sprintf(
                'The bucket public access prevention is inherited for %s.',
                self::$bucket->name()
            ),
            $output
        );

        self::$bucket->reload();
        $bucketInformation = self::$bucket->info();
        $pap = $bucketInformation['iamConfiguration']['publicAccessPrevention'];
        $this->assertEquals('inherited', $pap);
    }
}
