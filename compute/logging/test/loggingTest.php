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

require_once __DIR__ . '/mocks/FluentLogger.php';

class compute_loggingTest extends TestCase
{
    public function testExceptionHandler()
    {
        include __DIR__ . '/../index.php';
        global $logger;

        $lastHandler = set_exception_handler(null);
        $this->assertEquals('fluentd_exception_handler', $lastHandler);

        $exceptionMessage = 'testing exception handler';
        $e = new Exception($exceptionMessage);
        $lastHandler($e);
        $this->assertEquals($logger->prefix, 'myapp.errors');
        $this->assertEquals($exceptionMessage, $logger->msg['message']);
        $this->assertEquals($logger->msg['serviceContext'], ['service' => 'myapp']);
    }
}
