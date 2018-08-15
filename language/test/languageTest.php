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

namespace Google\Cloud\Samples\Language\Tests;

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for language commands.
 */
class languageTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;

    protected $gcsFile;

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
        $this->gcsFile = sprintf('gs://%s/language/presidents.txt', getenv('GOOGLE_STORAGE_BUCKET'));
    }

    public function testAll()
    {
        $output = $this->runCommand('all', 'Barack Obama lives in Washington D.C.');
        $this->assertContains('Name: Barack Obama', $output);
        $this->assertContains('Type: PERSON', $output);
        $this->assertContains('Salience:', $output);
        $this->assertContains('Wikipedia URL: https://en.wikipedia.org/wiki/Barack_Obama', $output);
        $this->assertContains('Knowledge Graph MID:', $output);
        $this->assertContains('Name: Washington D.C.', $output);
        $this->assertContains('Wikipedia URL: https://en.wikipedia.org/wiki/Washington,_D.C.', $output);
        $this->assertContains('Document Sentiment:', $output);
        $this->assertContains('Magnitude:', $output);
        $this->assertContains('Score:', $output);
        $this->assertContains('Sentence: Barack Obama lives in Washington D.C.', $output);
        $this->assertContains('Sentence Sentiment:', $output);
        $this->assertContains('  Magnitude:', $output);
        $this->assertContains('  Score:', $output);
        $this->assertContains('Token text: Barack', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: Obama', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: lives', $output);
        $this->assertContains('Token part of speech: VERB', $output);
        $this->assertContains('Token text: in', $output);
        $this->assertContains('Token part of speech: ADP', $output);
        $this->assertContains('Token text: Washington', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: D.C.', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
    }

    public function testAllFromStorageObject()
    {
        $this->checkBucketName();

        $output = $this->runCommand('all', $this->gcsFile);
        $this->assertContains('Name: Barack Obama', $output);
        $this->assertContains('Type: PERSON', $output);
        $this->assertContains('Salience:', $output);
        $this->assertContains('Wikipedia URL: https://en.wikipedia.org/wiki/Barack_Obama', $output);
        $this->assertContains('Knowledge Graph MID:', $output);
        $this->assertContains('Name: Washington D.C.', $output);
        $this->assertContains('Wikipedia URL: https://en.wikipedia.org/wiki/Washington,_D.C.', $output);
        $this->assertContains('Document Sentiment:', $output);
        $this->assertContains('Magnitude:', $output);
        $this->assertContains('Score:', $output);
        $this->assertContains('Sentence: Barack Obama lives in Washington D.C.', $output);
        $this->assertContains('Sentence Sentiment:', $output);
        $this->assertContains('  Magnitude:', $output);
        $this->assertContains('  Score:', $output);
        $this->assertContains('Token text: Barack', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: Obama', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: lives', $output);
        $this->assertContains('Token part of speech: VERB', $output);
        $this->assertContains('Token text: in', $output);
        $this->assertContains('Token part of speech: ADP', $output);
        $this->assertContains('Token text: Washington', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: D.C.', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
    }

    public function testEntities()
    {
        $output = $this->runCommand('entities', 'Barack Obama lives in Washington D.C.');
        $this->assertContains('Name: Barack Obama', $output);
        $this->assertContains('Type: PERSON', $output);
        $this->assertContains('Salience:', $output);
        $this->assertContains('Wikipedia URL: https://en.wikipedia.org/wiki/Barack_Obama', $output);
        $this->assertContains('Knowledge Graph MID:', $output);
        $this->assertContains('Name: Washington D.C.', $output);
        $this->assertContains('Wikipedia URL: https://en.wikipedia.org/wiki/Washington,_D.C.', $output);
    }


    public function testEntitiesFromStorageObject()
    {
        $this->checkBucketName();

        $output = $this->runCommand('entities', $this->gcsFile);
        $this->assertContains('Name: Barack Obama', $output);
        $this->assertContains('Type: PERSON', $output);
        $this->assertContains('Salience:', $output);
        $this->assertContains('Wikipedia URL: https://en.wikipedia.org/wiki/Barack_Obama', $output);
        $this->assertContains('Knowledge Graph MID:', $output);
        $this->assertContains('Name: Washington D.C.', $output);
        $this->assertContains('Wikipedia URL: https://en.wikipedia.org/wiki/Washington,_D.C.', $output);
    }

    public function testSentiment()
    {
        $output = $this->runCommand('sentiment', 'Barack Obama lives in Washington D.C.');
        $this->assertContains('Document Sentiment:', $output);
        $this->assertContains('Magnitude:', $output);
        $this->assertContains('Score:', $output);
        $this->assertContains('Sentence: Barack Obama lives in Washington D.C.', $output);
        $this->assertContains('Sentence Sentiment:', $output);
        $this->assertContains('  Magnitude:', $output);
        $this->assertContains('  Score:', $output);
    }


    public function testSentimentFromStorageObject()
    {
        $this->checkBucketName();

        $output = $this->runCommand('sentiment', $this->gcsFile);
        $this->assertContains('Document Sentiment:', $output);
        $this->assertContains('Magnitude:', $output);
        $this->assertContains('Score:', $output);
        $this->assertContains('Sentence: Barack Obama lives in Washington D.C.', $output);
        $this->assertContains('Sentence Sentiment:', $output);
        $this->assertContains('  Magnitude:', $output);
        $this->assertContains('  Score:', $output);
    }

    public function testSyntax()
    {
        $output = $this->runCommand('syntax', 'Barack Obama lives in Washington D.C.');
        $this->assertContains('Token text: Barack', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: Obama', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: lives', $output);
        $this->assertContains('Token part of speech: VERB', $output);
        $this->assertContains('Token text: in', $output);
        $this->assertContains('Token part of speech: ADP', $output);
        $this->assertContains('Token text: Washington', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: D.C.', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
    }

    public function testSyntaxFromStorageObject()
    {
        $this->checkBucketName();

        $output = $this->runCommand('syntax', $this->gcsFile);
        $this->assertContains('Token text: Barack', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: Obama', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: lives', $output);
        $this->assertContains('Token part of speech: VERB', $output);
        $this->assertContains('Token text: in', $output);
        $this->assertContains('Token part of speech: ADP', $output);
        $this->assertContains('Token text: Washington', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
        $this->assertContains('Token text: D.C.', $output);
        $this->assertContains('Token part of speech: NOUN', $output);
    }

    public function testEntitySentiment()
    {
        $output = $this->runCommand('entity-sentiment', 'Barack Obama lives in Washington D.C.');
        $this->assertContains('Entity Name: Barack Obama', $output);
        $this->assertContains('Entity Type: PERSON', $output);
        $this->assertContains('Entity Salience:', $output);
        $this->assertContains('Entity Magnitude:', $output);
        $this->assertContains('Entity Score:', $output);
        $this->assertContains('Entity Name: Washington D.C.', $output);
        $this->assertContains('Entity Type: LOCATION', $output);
    }

    public function testEntitySentimentFromStorageObject()
    {
        $this->checkBucketName();

        $output = $this->runCommand('entity-sentiment', $this->gcsFile);
        $this->assertContains('Entity Name: Barack Obama', $output);
        $this->assertContains('Entity Type: PERSON', $output);
        $this->assertContains('Entity Salience:', $output);
        $this->assertContains('Entity Magnitude:', $output);
        $this->assertContains('Entity Score:', $output);
        $this->assertContains('Entity Name: Washington D.C.', $output);
        $this->assertContains('Entity Type: LOCATION', $output);
    }

    public function testClassifyText()
    {
        $output = $this->runCommand(
            'classify', 'The first two gubernatorial elections since ' .
            'President Donald Trump took office went in favor of Democratic ' .
            'candidates in Virginia and New Jersey.');
        $this->assertContains('Category Name: /News/Politics', $output);
        $this->assertContains('Confidence:', $output);
    }

    public function testClassifyTextFromStorageObject()
    {
        $this->checkBucketName();

        $output = $this->runCommand('classify', $this->gcsFile);
        $this->assertContains('Category Name: /News/Politics', $output);
        $this->assertContains('Confidence:', $output);
    }

    private function runCommand($commandName, $content)
    {
        $application = require __DIR__ . '/../language.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute([
            'content' => $content,
        ], [
            'interactive' => false
        ]);
        return ob_get_clean();
    }

    private function checkBucketName()
    {
        if (!getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('Specify GOOGLE_STORAGE_BUCKET environment variable.');
        }
    }
}
