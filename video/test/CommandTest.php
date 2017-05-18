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


namespace Google\Cloud\Samples\Vision;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for transcribe commands.
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    /* @var $application Application */
    protected static $application;

    public static function setUpBeforeClass()
    {
        putenv('PHPUNIT_TESTS=1');
        self::$application = require __DIR__ . '/../video.php';
    }

    public function setUp()
    {
        if (!$creds = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }
    }

    public function testShotsCommand()
    {
        $commandTester = new CommandTester(self::$application->get('shots'));
        $commandTester->execute(
            [
                'uri' => 'gs://cloudmleap/video/next/fox-snatched.mp4',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('shot_annotations', $display);
        $this->assertContains('start_time_offset:', $display);
        $this->assertContains('end_time_offset:', $display);
    }
}
