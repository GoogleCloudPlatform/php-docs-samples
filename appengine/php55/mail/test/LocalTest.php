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

require_once __DIR__ . '/mocks/Message.php';

class LocalTest extends TestCase
{
    public function testSendMail()
    {
        ob_start();
        include __DIR__ . '/../index.php';
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertContains('Mail Sent', $result);
    }

    public function testIncomingHandle()
    {
        $_POST["content"] = '';

        ob_start();
        include __DIR__ . '/../handle_incoming_email.php';
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertContains('1', $result);
    }
    public function testBounceHandle()
    {
        $_POST["content"] = '';

        ob_start();
        include __DIR__ . '/../handle_bounced_email.php';
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertContains('1', $result);
    }
}
