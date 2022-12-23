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

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for language commands.
 */
class languageTest extends TestCase
{
    use TestTrait;

    public function gcsFile()
    {
        return sprintf(
            'gs://%s/language/presidents.txt',
            $this->requireEnv('GOOGLE_STORAGE_BUCKET')
        );
    }

    public function testAnalyzeAll()
    {
        $output = $this->runFunctionSnippet(
            'analyze_all',
            ['Barack Obama lives in Washington D.C.']
        );
        $this->assertStringContainsString('Name: Barack Obama', $output);
        $this->assertStringContainsString('Type: PERSON', $output);
        $this->assertStringContainsString('Salience:', $output);
        $this->assertStringContainsString('Wikipedia URL: https://en.wikipedia.org/wiki/Barack_Obama', $output);
        $this->assertStringContainsString('Knowledge Graph MID:', $output);
        $this->assertStringContainsString('Name: Washington D.C.', $output);
        $this->assertStringContainsString('Document Sentiment:', $output);
        $this->assertStringContainsString('Magnitude:', $output);
        $this->assertStringContainsString('Score:', $output);
        $this->assertStringContainsString('Sentence: Barack Obama lives in Washington D.C.', $output);
        $this->assertStringContainsString('Sentence Sentiment:', $output);
        $this->assertStringContainsString('  Magnitude:', $output);
        $this->assertStringContainsString('  Score:', $output);
        $this->assertStringContainsString('Token text: Barack', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: Obama', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: lives', $output);
        $this->assertStringContainsString('Token part of speech: VERB', $output);
        $this->assertStringContainsString('Token text: in', $output);
        $this->assertStringContainsString('Token part of speech: ADP', $output);
        $this->assertStringContainsString('Token text: Washington', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: D.C.', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
    }

    public function testAnalzeAllFromFile()
    {
        $output = $this->runFunctionSnippet('analyze_all_from_file', [$this->gcsFile()]);

        $this->assertStringContainsString('Name: Barack Obama', $output);
        $this->assertStringContainsString('Type: PERSON', $output);
        $this->assertStringContainsString('Salience:', $output);
        $this->assertStringContainsString('Wikipedia URL: https://en.wikipedia.org/wiki/Barack_Obama', $output);
        $this->assertStringContainsString('Knowledge Graph MID:', $output);
        $this->assertStringContainsString('Name: Washington D.C.', $output);
        $this->assertStringContainsString('Document Sentiment:', $output);
        $this->assertStringContainsString('Magnitude:', $output);
        $this->assertStringContainsString('Score:', $output);
        $this->assertStringContainsString('Sentence: Barack Obama lives in Washington D.C.', $output);
        $this->assertStringContainsString('Sentence Sentiment:', $output);
        $this->assertStringContainsString('  Magnitude:', $output);
        $this->assertStringContainsString('  Score:', $output);
        $this->assertStringContainsString('Token text: Barack', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: Obama', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: lives', $output);
        $this->assertStringContainsString('Token part of speech: VERB', $output);
        $this->assertStringContainsString('Token text: in', $output);
        $this->assertStringContainsString('Token part of speech: ADP', $output);
        $this->assertStringContainsString('Token text: Washington', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: D.C.', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
    }

    public function testAnalyzeEntities()
    {
        $output = $this->runFunctionSnippet('analyze_entities', [
            'Barack Obama lives in Washington D.C.'
        ]);
        $this->assertStringContainsString('Name: Barack Obama', $output);
        $this->assertStringContainsString('Type: PERSON', $output);
        $this->assertStringContainsString('Salience:', $output);
        $this->assertStringContainsString('Wikipedia URL: https://en.wikipedia.org/wiki/Barack_Obama', $output);
        $this->assertStringContainsString('Knowledge Graph MID:', $output);
        $this->assertStringContainsString('Name: Washington D.C.', $output);
    }

    public function testAnalyzeEntitiesFromFile()
    {
        $output = $this->runFunctionSnippet('analyze_entities_from_file', [
            $this->gcsFile()
        ]);
        $this->assertStringContainsString('Name: Barack Obama', $output);
        $this->assertStringContainsString('Type: PERSON', $output);
        $this->assertStringContainsString('Salience:', $output);
        $this->assertStringContainsString('Wikipedia URL: https://en.wikipedia.org/wiki/Barack_Obama', $output);
        $this->assertStringContainsString('Knowledge Graph MID:', $output);
        $this->assertStringContainsString('Name: Washington D.C.', $output);
    }

    public function testAnalyzeSentiment()
    {
        $output = $this->runFunctionSnippet('analyze_sentiment', [
            'Barack Obama lives in Washington D.C.'
        ]);
        $this->assertStringContainsString('Document Sentiment:', $output);
        $this->assertStringContainsString('Magnitude:', $output);
        $this->assertStringContainsString('Score:', $output);
        $this->assertStringContainsString('Sentence: Barack Obama lives in Washington D.C.', $output);
        $this->assertStringContainsString('Sentence Sentiment:', $output);
        $this->assertStringContainsString('  Magnitude:', $output);
        $this->assertStringContainsString('  Score:', $output);
    }

    public function testAnalyzeSentimentFromFile()
    {
        $output = $this->runFunctionSnippet('analyze_sentiment_from_file', [
            $this->gcsFile()
        ]);
        $this->assertStringContainsString('Document Sentiment:', $output);
        $this->assertStringContainsString('Magnitude:', $output);
        $this->assertStringContainsString('Score:', $output);
        $this->assertStringContainsString('Sentence: Barack Obama lives in Washington D.C.', $output);
        $this->assertStringContainsString('Sentence Sentiment:', $output);
        $this->assertStringContainsString('  Magnitude:', $output);
        $this->assertStringContainsString('  Score:', $output);
    }

    public function testAnalyzeSyntax()
    {
        $output = $this->runFunctionSnippet('analyze_syntax', [
            'Barack Obama lives in Washington D.C.'
        ]);
        $this->assertStringContainsString('Token text: Barack', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: Obama', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: lives', $output);
        $this->assertStringContainsString('Token part of speech: VERB', $output);
        $this->assertStringContainsString('Token text: in', $output);
        $this->assertStringContainsString('Token part of speech: ADP', $output);
        $this->assertStringContainsString('Token text: Washington', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: D.C.', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
    }

    public function testAnalyzeSyntaxFromFile()
    {
        $output = $this->runFunctionSnippet('analyze_syntax_from_file', [
            $this->gcsFile()
        ]);
        $this->assertStringContainsString('Token text: Barack', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: Obama', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: lives', $output);
        $this->assertStringContainsString('Token part of speech: VERB', $output);
        $this->assertStringContainsString('Token text: in', $output);
        $this->assertStringContainsString('Token part of speech: ADP', $output);
        $this->assertStringContainsString('Token text: Washington', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
        $this->assertStringContainsString('Token text: D.C.', $output);
        $this->assertStringContainsString('Token part of speech: NOUN', $output);
    }

    public function testAnalyzeEntitySentiment()
    {
        $output = $this->runFunctionSnippet('analyze_entity_sentiment', [
            'Barack Obama lives in Washington D.C.'
        ]);
        $this->assertStringContainsString('Entity Name: Barack Obama', $output);
        $this->assertStringContainsString('Entity Type: PERSON', $output);
        $this->assertStringContainsString('Entity Salience:', $output);
        $this->assertStringContainsString('Entity Magnitude:', $output);
        $this->assertStringContainsString('Entity Score:', $output);
        $this->assertStringContainsString('Entity Name: Washington D.C.', $output);
        $this->assertStringContainsString('Entity Type: LOCATION', $output);
    }

    public function testAnalyzeEntitySentimentFromFile()
    {
        $output = $this->runFunctionSnippet('analyze_entity_sentiment_from_file', [
            $this->gcsFile()
        ]);
        $this->assertStringContainsString('Entity Name: Barack Obama', $output);
        $this->assertStringContainsString('Entity Type: PERSON', $output);
        $this->assertStringContainsString('Entity Salience:', $output);
        $this->assertStringContainsString('Entity Magnitude:', $output);
        $this->assertStringContainsString('Entity Score:', $output);
        $this->assertStringContainsString('Entity Name: Washington D.C.', $output);
        $this->assertStringContainsString('Entity Type: LOCATION', $output);
    }

    public function testClassifyText()
    {
        $output = $this->runFunctionSnippet('classify_text', [
            'The first two gubernatorial elections since President '
                . 'Donald Trump took office went in favor of Democratic '
                . 'candidates in Virginia and New Jersey.'
        ]);
        $this->assertStringContainsString('Category Name: /News/Politics', $output);
        $this->assertStringContainsString('Confidence:', $output);
    }

    public function testClassifyTextFromFile()
    {
        $output = $this->runFunctionSnippet('classify_text_from_file', [
            $this->gcsFile()
        ]);
        $this->assertStringContainsString('Category Name: /News/Politics', $output);
        $this->assertStringContainsString('Confidence:', $output);
    }
}
