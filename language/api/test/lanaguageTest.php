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

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
    }

    public function testAll()
    {
        $output = $this->runCommand('all', 'Do you know the way to San Jose?');

        $expectedPatterns = [
            '/content: know/',
            '/type: PROPER/'
        ];

        foreach ($expectedPatterns as $expectedPattern) {
            $this->assertRegExp($expectedPattern, $output);
        }
    }

    public function testAllFromStorageObject()
    {
        if (!$gcsFile = getenv('GOOGLE_LANGUAGE_GCS_FILE')) {
            $this->markTestSkipped('No GCS file.');
        }

        $output = $this->runCommand('all', $gcsFile);

        $expectedPatterns = [
            '/content: know/',
            '/type: PROPER/'
        ];

        foreach ($expectedPatterns as $expectedPattern) {
            $this->assertRegExp($expectedPattern, $output);
        }
    }

    public function testEntities()
    {
        $output = $this->runCommand('entities', 'Do you know the way to San Jose?');

        $this->assertRegExp('/type: PROPER/', $output);
    }

    public function testEntitiesFromStorageObject()
    {
        if (!$gcsFile = getenv('GOOGLE_LANGUAGE_GCS_FILE')) {
            $this->markTestSkipped('No GCS file.');
        }

        $output = $this->runCommand('entities', $gcsFile);

        $this->assertRegExp('/type: PROPER/', $output);
    }

    public function testSentiment()
    {
        $output = $this->runCommand('sentiment', 'Do you know the way to San Jose?');

        $this->assertRegExp('/sentiment/', $output);
    }

    public function testSentimentFromStorageObject()
    {
        if (!$gcsFile = getenv('GOOGLE_LANGUAGE_GCS_FILE')) {
            $this->markTestSkipped('No GCS file.');
        }

        $output = $this->runCommand('sentiment', $gcsFile);

        $this->assertRegExp('/sentiment/', $output);
    }

    public function testSyntax()
    {
        $output = $this->runCommand('syntax', 'Do you know the way to San Jose?');

        $this->assertRegExp('/Do you know the way/', $output);
    }

    public function testSyntaxFromStorageObject()
    {
        if (!$gcsFile = getenv('GOOGLE_LANGUAGE_GCS_FILE')) {
            $this->markTestSkipped('No GCS file.');
        }

        $output = $this->runCommand('syntax', $gcsFile);

        $this->assertRegExp('/Do you know the way/', $output);
    }

    private function runCommand($commandName, $content)
    {
        $application = require __DIR__ . '/../language.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'content' => $content,
        ], [
            'interactive' => false
        ]);

        return $commandTester->getDisplay();
    }
}
