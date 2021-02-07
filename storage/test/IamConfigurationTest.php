<?php
/**
 * Copyright 2019 Google Inc.
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
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for IamConfiguration.
 * @group storage-iamconfiguration
 */
class IamConfigurationTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../storage.php';
    protected $storage;
    protected $bucket;

    public function setUp(): void
    {
        // Sleep to avoid the rate limit for creating/deleting.
        sleep(5 + rand(2, 4));
        $this->storage = new StorageClient();

        // Append random because tests for multiple PHP versions were running at the same time.
        $bucketName = 'php-iamconfiguration-' . time() . '-' . rand(1000, 9999);
        $this->bucket = $this->storage->createBucket($bucketName);
    }

    public function tearDown(): void
    {
        $this->bucket->delete();
    }

    public function testEnableUniformBucketLevelAccess()
    {
        $output = $this->runCommand('uniform-bucket-level-access', [
            'bucket' => $this->bucket->name(),
            '--enable' => true,
        ]);
        $outputString = <<<EOF
Uniform bucket-level access was enabled for {$this->bucket->name()}

EOF;
        $this->assertEquals($outputString, $output);
        $this->bucket->reload();
        $bucketInformation = $this->bucket->info();
        $ubla = $bucketInformation['iamConfiguration']['uniformBucketLevelAccess'];
        $this->assertTrue($ubla['enabled']);
    }

    /** @depends testEnableUniformBucketLevelAccess */
    public function testDisableUniformBucketLevelAccess()
    {
        $output = $this->runCommand('uniform-bucket-level-access', [
            'bucket' => $this->bucket->name(),
            '--disable' => true,
        ]);

        $outputString = <<<EOF
Uniform bucket-level access was disabled for {$this->bucket->name()}

EOF;
        $this->assertEquals($outputString, $output);
        $this->bucket->reload();
        $bucketInformation = $this->bucket->info();
        $ubla = $bucketInformation['iamConfiguration']['uniformBucketLevelAccess'];
        $this->assertFalse($ubla['enabled']);
    }

    /** @depends testDisableUniformBucketLevelAccess */
    public function testGetUniformBucketLevelAccess()
    {
        $output = $this->runCommand('uniform-bucket-level-access', [
            'bucket' => $this->bucket->name(),
            '--get' => true,
        ]);

        $outputString = <<<EOF
Uniform bucket-level access is disabled for {$this->bucket->name()}

EOF;
        $this->assertEquals($outputString, $output);
        $this->bucket->reload();
        $bucketInformation = $this->bucket->info();
        $ubla = $bucketInformation['iamConfiguration']['uniformBucketLevelAccess'];
        $this->assertFalse($ubla['enabled']);
    }

    public function testSetPublicAccessPreventionToEnforced()
    {
        $output = $this->runCommand('public-access-prevention', [
            'bucket' => $this->bucket->name(),
            'value' => 'enforced',
        ]);
        $outputString = <<<EOF
Public Access Prevention has been set to enforced for {$this->bucket->name()}.

EOF;
        $this->assertEquals($outputString, $output);
        $this->bucket->reload();
        $bucketInformation = $this->bucket->info();
        $pap = $bucketInformation['iamConfiguration']['publicAccessPrevention'];
        $this->assertEquals('enforced', $pap);
    }

    /** @depends testSetPublicAccessPreventionToEnforced */
    public function testSetPublicAccessPreventionToUnspecified()
    {
        $output = $this->runCommand('public-access-prevention', [
            'bucket' => $this->bucket->name(),
            'value' => 'unspecified',
        ]);

        $outputString = <<<EOF
Public Access Prevention has been set to unspecified for {$this->bucket->name()}.

EOF;
        $this->assertEquals($outputString, $output);
        $this->bucket->reload();
        $bucketInformation = $this->bucket->info();
        $pap = $bucketInformation['iamConfiguration']['publicAccessPrevention'];
        $this->assertEquals('unspecified', $pap);
    }

    /** @depends testSetPublicAccessPreventionToUnspecified */
    public function testGetPublicAccessPrevention()
    {
        $output = $this->runCommand('public-access-prevention', [
            'bucket' => $this->bucket->name(),
        ]);

        $outputString = <<<EOF
The bucket public access prevention is unspecified for {$this->bucket->name()}.

EOF;
        $this->assertEquals($outputString, $output);
        $this->bucket->reload();
        $bucketInformation = $this->bucket->info();
        $pap = $bucketInformation['iamConfiguration']['publicAccessPrevention'];
        $this->assertEquals('unspecified', $pap);
    }
}
