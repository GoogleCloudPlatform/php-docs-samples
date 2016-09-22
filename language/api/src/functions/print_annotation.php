<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Language;

use Google\Cloud\NaturalLanguage\Annotation;

function print_annotation(Annotation $annotation)
{
    $info = $annotation->info();
    if (isset($info['language'])) {
        printf('language: %s' . PHP_EOL, $info['language']);
    }

    if (isset($info['documentSentiment'])) {
        $magnitude = $info['documentSentiment']['magnitude'];
        $polarity = $info['documentSentiment']['polarity'];
        if ($polarity < 0) {
            $magnitude = -$magnitude;
        }
        printf('sentiment: %s' . PHP_EOL, $magnitude);
    }

    if (isset($info['sentences'])) {
        print 'sentences:' . PHP_EOL;
        foreach ($info['sentences'] as $sentence) {
            printf('  %s: %s' . PHP_EOL,
                $sentence['text']['beginOffset'],
                $sentence['text']['content']);
        }
    }

    if (isset($info['tokens'])) {
        print 'tokens:' . PHP_EOL;
        foreach ($info['tokens'] as $token) {
            printf('  %s: %s' . PHP_EOL,
                $token['lemma'],
                $token['partOfSpeech']['tag']);
        }
    }

    if (isset($info['entities'])) {
        print 'entities:' . PHP_EOL;
        foreach ($info['entities'] as $entity) {
            printf('  %s', $entity['name']);
            if (isset($entity['metadata']['wikipedia_url'])) {
                printf(': %s', $entity['metadata']['wikipedia_url']);
            }
            print PHP_EOL;
        }
    }
}
