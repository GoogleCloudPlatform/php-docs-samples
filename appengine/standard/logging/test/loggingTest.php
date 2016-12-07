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

use google\appengine\api\log\LogService;

class loggingTest extends PHPUnit_Framework_TestCase
{
    public function testNoLogs()
    {
        ob_start();
        include __DIR__ . '/../index.php';
        $result = ob_get_contents();
        ob_end_clean();
        // Make sure it looks like Shakespeare.
        $this->assertContains('No logs!', $result);
    }

    public function testSomeLogs()
    {
        $log1 = $this->getMock('google\appengine\api\log\RequestLog');
        $applog1 = $this->getMock('google\appengine\api\log\AppLogLine');
        $expectedLog = [
            [ 'method' => 'getIp', 'return' => '127.0.0.1' ],
            [ 'method' => 'getStatus', 'return' => 'log-status-1' ],
            [ 'method' => 'getMethod', 'return' => 'log-method-1' ],
            [ 'method' => 'getResource', 'return' => 'log-resource-1' ],
            [ 'method' => 'getEndDateTime', 'return' => $d1 = new DateTime() ],
            [ 'method' => 'getAppLogs', 'return' => [ $applog1 ] ],
        ];
        $expectedAppLog = [
            [ 'method' => 'getMessage', 'return' => 'applog-message-1' ],
            [ 'method' => 'getDateTime', 'return' => $d2 = new DateTime('-1 hour') ],
        ];
        foreach ($expectedLog as $expected) {
            $log1->expects($this->once())
                ->method($expected['method'])
                ->will($this->returnValue($expected['return']));
        }
        foreach ($expectedAppLog as $expected) {
            $applog1->expects($this->once())
                ->method($expected['method'])
                ->will($this->returnValue($expected['return']));
        }

        LogService::$logs = [ $log1 ];
        ob_start();
        include __DIR__ . '/../index.php';
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertContains('127.0.0.1', $result);
        $this->assertContains('log-status-1', $result);
        $this->assertContains('log-method-1', $result);
        $this->assertContains('log-resource-1', $result);
        $this->assertContains($d1->format('c'), $result);
        $this->assertContains('applog-message-1', $result);
        $this->assertContains($d2->format('c'), $result);
    }

    public function testSyslog()
    {
        // not authorized
        ob_start();
        include __DIR__ . '/../syslog.php';
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('false', $result);

        // authorized
        $_GET['authorized'] = 1;
        ob_start();
        include __DIR__ . '/../syslog.php';
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('true', $result);
    }

    public function testTimeout()
    {
        global $startTime;
        $startTime = time();

        // timeout after 2 seconds
        ob_start();
        include __DIR__ . '/../timeout.php';
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('Got timeout! Cleaning up...', $result);
        $this->assertGreaterThan($startTime + 2, time());
    }
}
