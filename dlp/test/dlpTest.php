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
namespace Google\Cloud\Samples\Dlp;

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for dlp commands.
 */
class dlpTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }
    }

    public function testInspectDatastore()
    {
        $output = $this->runCommand('inspect-datastore', [
            'kind' => 'Book',
            'project' => getenv('GOOGLE_PROJECT_ID'),
        ]);
        $this->assertContains('US_MALE_NAME', $output);
    }

    public function testInspectBigquery()
    {
        $output = $this->runCommand('inspect-bigquery', [
            'dataset' => 'integration_tests_dlp',
            'table' => 'harmful',
            'project' => getenv('GOOGLE_PROJECT_ID'),
        ]);
        $this->assertContains('CREDIT_CARD_NUMBER', $output);
    }

    public function testInspectFile()
    {
        // inspect a text file with results
        $output = $this->runCommand('inspect-file', [
            'path' => __DIR__ . '/data/test.txt'
        ]);
        $this->assertContains('US_MALE_NAME', $output);
        $this->assertContains('Very likely', $output);

        // inspect an image file with results
        $output = $this->runCommand('inspect-file', [
            'path' => __DIR__ . '/data/test.png'
        ]);
        $this->assertContains('US_MALE_NAME', $output);
        $this->assertContains('Very likely', $output);

        // inspect a file with no results
        $output = $this->runCommand('inspect-file', [
            'path' => __DIR__ . '/data/harmless.txt'
        ]);
        $this->assertContains('No findings', $output);
    }

    public function testInspectString()
    {
        // inspect a string with results
        $output = $this->runCommand('inspect-string', [
            'string' => 'The name Robert is very common.'
        ]);
        $this->assertContains('US_MALE_NAME', $output);
        $this->assertContains('Very likely', $output);

        // inspect a string with no results
        $output = $this->runCommand('inspect-string', [
            'string' => 'The name Zolo is not very common.'
        ]);
        $this->assertContains('No findings', $output);
    }

    public function testListCategories()
    {
        $output = $this->runCommand('list-categories');
        $this->assertContains('Personally identifiable information', $output);
    }

    public function testListInfoTypes()
    {
        // list all info types
        $output = $this->runCommand('list-info-types');
        $this->assertContains('US_DEA_NUMBER', $output);
        $this->assertContains('AMERICAN_BANKERS_CUSIP_ID', $output);

        // list info types by category
        $output = $this->runCommand('list-info-types', [
            'category' => 'GOVERNMENT'
        ]);

        $this->assertContains('US_DEA_NUMBER', $output);
        $this->assertNotContains('AMERICAN_BANKERS_CUSIP_ID', $output);
    }

    public function testRedactString()
    {
        $output = $this->runCommand('redact-string', [
            'string' => 'The name Robert is very common.'
        ]);
        $this->assertContains('The name xxx is very common', $output);


        $output = $this->runCommand('redact-string', [
            'string' => 'The name Zolo is not very common.'
        ]);
        $this->assertContains('The name Zolo is not very common', $output);
    }

    private function runCommand($commandName, $args = [])
    {
        $application = require __DIR__ . '/../dlp.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute(
            $args,
            ['interactive' => false]);

        return ob_get_clean();
    }
}
