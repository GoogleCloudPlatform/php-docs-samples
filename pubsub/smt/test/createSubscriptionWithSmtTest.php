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

use Google\Cloud\PubSub\PubSubClient;
use PHPUnit\Framework\TestCase;

class createSubscriptionWithSmtTest extends TestCase
{
    private $subscription;
    private $topicName;
    private $topic;

    public function setUp(): void
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            throw new Exception('GOOGLE_APPLICATION_CREDENTIALS must be set.');
        }
        $pubsub = new PubSubClient([
            'projectId' => $projectId
        ]);

        $this->topicName = 'my-new-smt-topic-' . time();
        $this->topic = $pubsub->createTopic($this->topicName);
    }

    public function testCreateSubscriptionWithSmt()
    {
        $topicName = $this->topicName;
        $subscriptionName = 'my-new-smt-subscription-' . time();
        $file = sys_get_temp_dir() . '/pubsub_createSubscriptionWithSmt.php';
        $contents = file_get_contents(__DIR__ . '/../createSubscriptionWithSmt.php');
        $contents = str_replace(
            ['smt-topic', 'smt-subscription', 'YOUR_PROJECT_ID', '__DIR__'],
            [$topicName, $subscriptionName, $projectId, sprintf('"%s/.."', __DIR__)],
            $contents
        );
        file_put_contents($file, $contents);

        // Invoke createSubscriptionWithSmt.php
        ob_start();
        $this->subscription = include $file;
        $output = ob_get_clean();

        // Make sure it looks correct
        $this->assertInstanceOf('Google\Cloud\PubSub\Subscription', $this->subscription);
        $this->assertStringContainsString($subscriptionName, $this->subscription->name());
    }

    public function tearDown(): void
    {
        if ($this->subscription) {
            $this->subscription->delete();
        }
        if ($this->topic) {
            $this->topic->delete();
        }
    }
}
