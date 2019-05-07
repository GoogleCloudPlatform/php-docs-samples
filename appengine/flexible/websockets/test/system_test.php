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
namespace Google\Cloud\Test;

use PHPUnit\Framework\TestCase;
use \Ratchet\Client;
use \React\Promise\Promise;
use \React\EventLoop;
use \Clue\React\Block;

require __DIR__ . '/../vendor/autoload.php';

class LocalTest extends TestCase
{
    protected $loop;

    protected function setUp()
    {
        $this->loop = EventLoop\Factory::create();

        parent::setUp();
    }

    public function testIndex()
    {
        $connector = new Client\Connector($this->loop);
        $basePromise = $connector('ws://' . getenv('GCLOUD_PROJECT') . '.appspot.com/ws')
            ->then(function ($conn) {
                $endPromise = new Promise(function ($resolve) use ($conn) {
                    $conn->on('message', function ($msg) use ($resolve, $conn) {
                        $conn->close();
                        $resolve($msg);
                    });
                    $conn->send('Hello World!');
                });
                return $endPromise;
            })
            ->otherwise(function ($e) {
                echo "Error: " . $e->getMessage() . "\n";
                throw $e;
            });

        $this->loop->run();
        $resolvedMsg = Block\await($basePromise, $this->loop);
        $this->assertContains(
            "Message received: Hello World!",
            strval($resolvedMsg)
        );
    }
}
