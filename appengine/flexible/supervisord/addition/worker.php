<?php
/**
 * Copyright 2018 Google Inc.
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

// This is a dumb worker process for just updating the predefined file with a
// random quote.

$nextFile = '/tmp/nextQuote';
$currentFile = '/tmp/currentQuote';

set_time_limit(0);

while (1) {
    $quote = file_get_contents(
        'https://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=json'
    );
    if ($quote) {
        $jsonQuote = json_decode($quote);
        if (file_put_contents($nextFile, $jsonQuote->quoteText)) {
            // Atomic update
            rename($nextFile, $currentFile);
        }
    }
    // Update the quote every hour.
    sleep(3600);
}
