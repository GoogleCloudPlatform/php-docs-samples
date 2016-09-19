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

function print_annotation(Annotation $annotation) {
    $info = $annotation->info();
    if (isset($info['language'])) {
        print "language: " . $info['language'] . "\n";
    }

    if (isset($info['documentSentiment']))
    {
        $magnitude = $info['documentSentiment']['magnitude'];
        $polarity = $info['documentSentiment']['polarity'];
        if ($polarity < 0)
            $magnitude = -$magnitude;
        print "sentiment: " . $magnitude . "\n";
    }

    if (isset($info['sentences']))
    {
        print "sentences:\n";
        foreach ($info['sentences'] as $sentence) {
            print "  " . $sentence['text']['beginOffset'] . ": " .
                $sentence['text']['content'] . "\n";
        }
    }

    if (isset($info['entities']))
    {
        print "entities:\n";
        foreach ($info['entities'] as $entity) {
            print "  " . $entity['name'];
            if (isset($entity['metadata']['wikipedia_url']))
                print ": " .  $entity['metadata']['wikipedia_url'];
            print "\n";
        }
    }
}