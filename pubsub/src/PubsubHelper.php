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

namespace GoogleCloudPlatform\DocsSamples\Pubsub;

/**
 * Utility class for making calls to the PubSub API. Functions have been written
 * specifically for the PubSub sample application.
 */
class PubsubHelper
{
    /**
     * Creates the PubSub topic for the supplied $projetId if it doesn't exist
     */
    public function setupTopic($projectId, $topicName, \Google_Service_Pubsub $pubsub)
    {
        $service = $pubsub->projects_topics;
        $topics = $service->listProjectsTopics($projectId);
        $topic = null;
        foreach ($topics->getTopics() as $projectTopic) {
            if ($projectTopic->getName() == $topicName) {
                $topic = $projectTopic;
                break;
            }
        }

        // create the default topic
        if (is_null($topic)) {
            $topic = new \Google_Service_Pubsub_Topic();
            $topic->setName($topicName);
            $service->create($topicName, $topic);
        }

        return true;
    }

    /**
     * Subscribes this app to the PubSub topic if the topic has no subscribers
     */
    public function setupSubscription($projectId, $topicName, $subscriptionName, $token, \Google_Service_Pubsub $pubsub)
    {
        $fullSubscriptionName = sprintf('projects/%s/subscriptions/%s', $projectId, $subscriptionName);

        try {
            // NOTE: if the version changes, the subscription will
            // continue to point to the older version, so you'll need to
            // delete the subscription in Google Developer Console in
            // order for Cloud PubSub to push to newer versions
            return $pubsub->projects_subscriptions->get($fullSubscriptionName);
        } catch (\Google_Service_Exception $e) {
            if ($e->getCode() != 404) {
                throw $e;
            }
        }

        $endpoint = $this->getEndpoint($projectId, $token);

        $subscription = new \Google_Service_Pubsub_Subscription();
        $pushConfig = new \Google_Service_Pubsub_PushConfig();
        $pushConfig->setPushEndpoint($endpoint);
        $subscription->setPushConfig($pushConfig);
        $subscription->setTopic($topicName);

        return $pubsub->projects_subscriptions->create($fullSubscriptionName, $subscription);
    }

    /**
     * Returns the Push endpoint for this application to use in the Cloud PubSub
     * subscription. The endpoint is created from the AppEngine Version ID and
     * the pubsub token.
     */
    public function getEndpoint($projectId, $token)
    {
        $versionSubdomain = '';
        $version = getenv('CURRENT_VERSION_ID');

        // CURRENT_VERSION_ID in PHP represents major and minor version
        if (1 === substr_count($version, '.')) {
            list($major, $minor) = explode('.', $version);
            $versionSubdomain = $major . '-dot-';
        }

        return sprintf('https://%s%s.appspot.com/receive_message?token=%s',
            $versionSubdomain,
            $projectId,
            $token
        );
    }
}