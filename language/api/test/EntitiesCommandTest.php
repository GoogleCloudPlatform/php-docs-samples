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

namespace Google\Cloud\Samples\Language\Tests;

use Google\Cloud\Samples\Language\EntitiesCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for EntitiesCommand.
 */
class EntitiesCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;

    /* @var CommandTester $commandTester */
    private $commandTester;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        $application = new Application();
        $application->add(new EntitiesCommand());
        $this->commandTester = new CommandTester($application->get('entities'));
    }

    public function testEntities()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        $this->commandTester->execute(
            ['content' =>  explode(' ', 'Do you know the way to San Jose?')],
            ['interactive' => false]
        );

        $output = $this->commandTester->getDisplay();
        $this->assertRegExp('/http:\/\/en.wikipedia.org/', $output);
    }

    public function testEntitiesFromStorageObject()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$gcsFile = getenv('GOOGLE_LANGUAGE_GCS_FILE')) {
            $this->markTestSkipped('No GCS file.');
        }

        $this->commandTester->execute(
            ['content' =>  $gcsFile],
            ['interactive' => false]
        );

        $output = $this->commandTester->getDisplay();
        $this->assertRegExp('/http:\/\/en.wikipedia.org/', $output);
    }
}
