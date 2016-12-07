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
class shakespeareTest extends PHPUnit_Framework_TestCase
{
    public function testShakespeare()
    {
        global $argv;
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('GOOGLE_PROJECT_ID must be set.');
        }
        $argv[1] = $projectId;

        $outputString = <<<EOF
--- Row 1 ---
title: hamlet
unique_words: 5318
--- Row 2 ---
title: kinghenryv
unique_words: 5104
--- Row 3 ---
title: cymbeline
unique_words: 4875
--- Row 4 ---
title: troilusandcressida
unique_words: 4795
--- Row 5 ---
title: kinglear
unique_words: 4784
--- Row 6 ---
title: kingrichardiii
unique_words: 4713
--- Row 7 ---
title: 2kinghenryvi
unique_words: 4683
--- Row 8 ---
title: coriolanus
unique_words: 4653
--- Row 9 ---
title: 2kinghenryiv
unique_words: 4605
--- Row 10 ---
title: antonyandcleopatra
unique_words: 4582
Found 10 row(s)

EOF;

        // Invoke shakespeare.php
        include __DIR__ . '/../shakespeare.php';

        // Make sure it looks correct
        $this->expectOutputString($outputString);
    }
}
