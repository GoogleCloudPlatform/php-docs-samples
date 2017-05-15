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

namespace Google\Cloud\Samples\Language;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility for the Natural Language APIs.
 *
 * Usage: php language.php syntax TEXT
 */
class SyntaxCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('syntax')
            ->setDescription('Analyze syntax in text.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command analyzes text using the Google Cloud Natural Language API.

    <info>php %command.full_name% Text to analyze.</info>

    <info>php %command.full_name% gs://my_bucket/file_with_text.txt</info>
    
Example:
    <info>php %command.full_name% "John took a walk."</info>
sentences:
  -
    text:
      content: 'John took a walk.'
      beginOffset: 0
tokens:
  -
    text:
      content: John
      beginOffset: 0
    partOfSpeech:
      tag: NOUN
      aspect: ASPECT_UNKNOWN
      case: CASE_UNKNOWN
      form: FORM_UNKNOWN
      gender: GENDER_UNKNOWN
      mood: MOOD_UNKNOWN
      number: SINGULAR
      person: PERSON_UNKNOWN
      proper: PROPER
      reciprocity: RECIPROCITY_UNKNOWN
      tense: TENSE_UNKNOWN
      voice: VOICE_UNKNOWN
    dependencyEdge:
      headTokenIndex: 1
      label: NSUBJ
    lemma: John
  -
    text:
      content: took
      beginOffset: 5
    partOfSpeech:
      tag: VERB
      aspect: ASPECT_UNKNOWN
      case: CASE_UNKNOWN
      form: FORM_UNKNOWN
      gender: GENDER_UNKNOWN
      mood: INDICATIVE
      number: NUMBER_UNKNOWN
      person: PERSON_UNKNOWN
      proper: PROPER_UNKNOWN
      reciprocity: RECIPROCITY_UNKNOWN
      tense: PAST
      voice: VOICE_UNKNOWN
    dependencyEdge:
      headTokenIndex: 1
      label: ROOT
    lemma: take
  -
    text:
      content: a
      beginOffset: 10
    partOfSpeech:
      tag: DET
      aspect: ASPECT_UNKNOWN
      case: CASE_UNKNOWN
      form: FORM_UNKNOWN
      gender: GENDER_UNKNOWN
      mood: MOOD_UNKNOWN
      number: NUMBER_UNKNOWN
      person: PERSON_UNKNOWN
      proper: PROPER_UNKNOWN
      reciprocity: RECIPROCITY_UNKNOWN
      tense: TENSE_UNKNOWN
      voice: VOICE_UNKNOWN
    dependencyEdge:
      headTokenIndex: 3
      label: DET
    lemma: a
  -
    text:
      content: walk
      beginOffset: 12
    partOfSpeech:
      tag: NOUN
      aspect: ASPECT_UNKNOWN
      case: CASE_UNKNOWN
      form: FORM_UNKNOWN
      gender: GENDER_UNKNOWN
      mood: MOOD_UNKNOWN
      number: SINGULAR
      person: PERSON_UNKNOWN
      proper: PROPER_UNKNOWN
      reciprocity: RECIPROCITY_UNKNOWN
      tense: TENSE_UNKNOWN
      voice: VOICE_UNKNOWN
    dependencyEdge:
      headTokenIndex: 1
      label: DOBJ
    lemma: walk
  -
    text:
      content: .
      beginOffset: 16
    partOfSpeech:
      tag: PUNCT
      aspect: ASPECT_UNKNOWN
      case: CASE_UNKNOWN
      form: FORM_UNKNOWN
      gender: GENDER_UNKNOWN
      mood: MOOD_UNKNOWN
      number: NUMBER_UNKNOWN
      person: PERSON_UNKNOWN
      proper: PROPER_UNKNOWN
      reciprocity: RECIPROCITY_UNKNOWN
      tense: TENSE_UNKNOWN
      voice: VOICE_UNKNOWN
    dependencyEdge:
      headTokenIndex: 1
      label: P
    lemma: .
language: en
entities: {  }
EOF
            )
            ->addArgument(
                'content',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Text or path to Cloud Storage file'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            $result = analyze_syntax_from_file($matches[1], $matches[2]);
        } else {
            $result = analyze_syntax($content);
        }
        $output->write(annotation_to_string($result));
    }
}
