<?php
/**
 * Copyright 2017 Google Inc.
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

use Google\Cloud\Samples\Storage\BucketLabelsCommand;
use Google\Cloud\Storage\StorageClient;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for BucketLabelsCommand.
 */
class BucketLabelsCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;
    protected $commandTester;
    protected $storage;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        $application = require __DIR__ . '/../storage.php';
        $this->commandTester = new CommandTester($application->get('bucket-labels'));
        $this->storage = new StorageClient();
    }

    public function testManageBucketLabels()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }

        $label1 = 'label1-' . time();
        $label2 = 'label2-' . time();
        $value1 = 'value1-' . time();
        $value2 = 'value2-' . time();
        $value3 = 'value3-' . time();

        $output = $this->runLabelsCommand([
            'bucket' => $bucketName,
            'label' => $label1,
            '--value' => $value1
        ]);

        $this->assertEquals(sprintf(
            'Added label %s (%s) to %s' . PHP_EOL,
            $label1,
            $value1,
            $bucketName
        ), $output);

        $output = $this->runLabelsCommand(
            ['bucket' => $bucketName]
        );

        $this->assertContains(sprintf('%s: value1', $label1), $output);

        $output = $this->runLabelsCommand([
            'bucket' => $bucketName,
            'label' => $label2,
            '--value' => $value2,
        ]);

        $this->assertEquals(sprintf(
            'Added label %s (%s) to %s' . PHP_EOL,
            $label2,
            $value2,
            $bucketName
        ), $output);

        $output = $this->runLabelsCommand(
            ['bucket' => $bucketName]
        );

        $this->assertContains(sprintf('%s: %s', $label1, $value1), $output);
        $this->assertContains(sprintf('%s: %s', $label2, $value2), $output);

        $output = $this->runLabelsCommand([
            'bucket' => $bucketName,
            'label' => $label1,
            '--value' => $value3
        ]);

        $this->assertEquals(sprintf(
            'Added label %s (%s) to %s' . PHP_EOL,
            $label1,
            $value3,
            $bucketName
        ), $output);

        $output = $this->runLabelsCommand(
            ['bucket' => $bucketName]
        );

        $this->assertContains(sprintf('%s: %s', $label1, $value3), $output);
        $this->assertNotContains($value1, $output);

        $output = $this->runLabelsCommand([
            'bucket' => $bucketName,
            'label' => $label1,
            '--remove' => true
        ]);

        $this->assertEquals(sprintf(
            'Removed label %s from %s' . PHP_EOL,
            $label1,
            $bucketName
        ), $output);

        $output = $this->runLabelsCommand([
            'bucket' => $bucketName,
            'label' => $label2,
            '--remove' => true
        ]);

        $this->assertEquals(sprintf(
            'Removed label %s from %s' . PHP_EOL,
            $label2,
            $bucketName
        ), $output);

        $output = $this->runLabelsCommand(
            ['bucket' => $bucketName]
        );

        $this->assertNotContains($label1, $output);
        $this->assertNotContains($label2, $output);
    }

    private function runLabelsCommand($options = [])
    {
        ob_start();
        $this->commandTester->execute(
            $options,
            ['interactive' => false]
        );
        return ob_get_clean();
    }
}
