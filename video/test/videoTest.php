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
namespace Google\Cloud\Samples\VideoIntelligence;

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for video commands.
 */
class videoTest extends \PHPUnit_Framework_TestCase
{
    private static $gcsUri = 'gs://demomaker/cat.mp4';

    public function setUp()
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }
    }

    public function testAnalyzeFaces()
    {
        $output = $this->runCommand('faces', ['uri' => self::$gcsUri]);
        $this->assertContains('left:', $output);
    }

    public function testAnalyzeLabels()
    {
        $output = $this->runCommand('labels', ['uri' => self::$gcsUri]);
        $this->assertContains('cat', $output);
    }

    public function testAnalyzeLabelsInFile()
    {
        $output = $this->runCommand('labels-in-file', [
            'file' => __DIR__ . '/data/cat_shortened.mp4'
        ]);
        $this->assertContains('cat', $output);
    }

    public function testAnalyzeSafeSearch()
    {
        $output = $this->runCommand('safe-search', ['uri' => self::$gcsUri]);
        $this->assertContains('adult:', $output);
    }

    public function testAnalyzeShots()
    {
        $output = $this->runCommand('shots', ['uri' => self::$gcsUri]);
        $this->assertContains(' to ', $output);
    }

    private function runCommand($commandName, $args)
    {
        $application = require __DIR__ . '/../video.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute(
            $args,
            ['interactive' => false]);

        return ob_get_clean();
    }
}
