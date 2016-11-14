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

/**
 * Convert an Annotation to a string.
 *
 * @param Annotation $annotation
 * @return string
 */
function annotation_to_string(Annotation $annotation)
{
    $ret = '';
    $info = $annotation->info();
    if (isset($info['language'])) {
        $ret .= sprintf('language: %s' . PHP_EOL, $info['language']);
    }

    if (isset($info['documentSentiment'])) {
        $magnitude = $info['documentSentiment']['magnitude'];
        $score = $info['documentSentiment']['score'];
        $ret .= sprintf('sentiment magnitude: %s score: %s' . PHP_EOL,
            $magnitude, $score);
    }

    if (isset($info['sentences'])) {
        $ret .= 'sentences:' . PHP_EOL;
        foreach ($info['sentences'] as $sentence) {
            $ret .= sprintf('  %s: %s' . PHP_EOL,
                $sentence['text']['beginOffset'],
                $sentence['text']['content']);
        }
    }

    if (isset($info['tokens'])) {
        $ret .= 'tokens:' . PHP_EOL;
        foreach ($info['tokens'] as $token) {
            $ret .= sprintf('  %s: %s' . PHP_EOL,
                $token['lemma'],
                $token['partOfSpeech']['tag']);
        }
    }

    if (isset($info['entities'])) {
        $ret .= 'entities:' . PHP_EOL;
        foreach ($info['entities'] as $entity) {
            $ret .= sprintf('  %s', $entity['name']);
            if (isset($entity['metadata']['wikipedia_url'])) {
                $ret .= sprintf(': %s', $entity['metadata']['wikipedia_url']);
            }
            $ret .= PHP_EOL;
        }
    }
    return $ret;
}
