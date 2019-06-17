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

use PHPUnit\Framework\TestCase;

class stackoverflowTest extends TestCase
{
    public function testStackoverflow()
    {
        global $argv;
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('GOOGLE_PROJECT_ID must be set.');
        }
        $argv[1] = $projectId;

        // Invoke stackoverflow.php
        include __DIR__ . '/../stackoverflow.php';

        // Make sure it looks correct
        $this->expectOutputRegex('/stackoverflow\.com/');
        $this->expectOutputRegex('/views/');
    }
}
