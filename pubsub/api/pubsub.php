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

namespace Google\Cloud\Samples\PubSub;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$application = new Application();
$application->add(new Command('subscription'))
    ->setDescription('Manage subscriptions for Pub\Sub')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Pub\Sub subscriptions.

<info>php %command.full_name%</info>

EOF
    )
    ->addArgument('project', InputArgument::REQUIRED, 'Your Google Cloud project ID')
    ->addArgument('subscription', InputArgument::OPTIONAL, 'The subscription name')
    ->addOption('create', null, InputOption::VALUE_NONE, 'Create the subscription. ')
    ->addOption('topic', null, InputOption::VALUE_REQUIRED, 'The topic for the subscription (when using --create).')
    ->addOption('endpoint', null, InputOption::VALUE_REQUIRED, 'An optional endpoint for push subscriptions.')
    ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete the subscription.')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $subscriptionName = $input->getArgument('subscription');
        if (empty($subscriptionName)) {
            list_subscriptions($projectId);
        } elseif ($input->getOption('create')) {
            if (!$topicName = $input->getOption('topic')) {
                throw new \Exception('--topic is required when creating a subscription');
            }
            if ($endpoint = $input->getOption('endpoint')) {
                create_push_subscription($projectId, $topicName, $subscriptionName, $endpoint);
            } else {
                create_subscription($projectId, $topicName, $subscriptionName);
            }
        } elseif ($input->getOption('delete')) {
            delete_subscription($projectId, $subscriptionName);
        } else {
            pull_messages($projectId, $subscriptionName);
        }
    });

$application->add(new Command('topic'))
    ->setDescription('Manage topics for Pub\Sub')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Pub\Sub topics.

<info>php %command.full_name%</info>

EOF
    )
    ->addArgument('project', InputArgument::REQUIRED, 'Your Google Cloud project ID')
    ->addArgument('topic', InputArgument::OPTIONAL, 'The topic name')
    ->addArgument('message', InputArgument::OPTIONAL, 'A message to publish to the topic')
    ->addOption('create', null, InputOption::VALUE_NONE, 'Create the topic. ')
    ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete the topic. ')
    ->addOption('batch', null, InputOption::VALUE_NONE, 'Use the batch publisher.')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $topicName = $input->getArgument('topic');
        if (empty($topicName)) {
            list_topics($projectId);
        } elseif ($input->getOption('create')) {
            create_topic($projectId, $topicName);
        } elseif ($input->getOption('delete')) {
            delete_topic($projectId, $topicName);
        } elseif ($input->getOption('batch') && $message = $input->getArgument('message')) {
            publish_message_batch($projectId, $topicName, $message);
        } elseif ($message = $input->getArgument('message')) {
            publish_message($projectId, $topicName, $message);
        } else {
            throw new \Exception('Must provide "--create", "--delete" or "message" with topic name');
        }
    });

$application->add(new Command('iam'))
    ->setDescription('Manage IAM for Pub\Sub')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Pub\Sub IAM policies.

<info>php %command.full_name% --topic my-topic</info>

<info>php %command.full_name% --subscription my-subscription</info>

EOF
    )
    ->addArgument('project', InputArgument::REQUIRED, 'Your Google Cloud project ID')
    ->addOption('topic', null, InputOption::VALUE_REQUIRED, 'The topic name.')
    ->addOption('subscription', null, InputOption::VALUE_REQUIRED, 'The subscription name.')
    ->addOption('add-user', null, InputOption::VALUE_REQUIRED, 'Create the IAM for the supplied user email.')
    ->addOption('test', null, InputOption::VALUE_NONE, 'Test the IAM policy.')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $topicName = $input->getOption('topic');
        $subscriptionName = $input->getOption('subscription');
        if ($topicName) {
            if ($userEmail = $input->getOption('add-user')) {
                set_topic_policy($projectId, $topicName, $userEmail);
            } elseif ($input->getOption('test')) {
                test_topic_permissions($projectId, $topicName);
            } else {
                get_topic_policy($projectId, $topicName);
            }
        } elseif ($subscriptionName) {
            if ($userEmail = $input->getOption('add-user')) {
                set_subscription_policy($projectId, $subscriptionName, $userEmail);
            } elseif ($input->getOption('test')) {
                test_subscription_permissions($projectId, $subscriptionName);
            } else {
                get_subscription_policy($projectId, $subscriptionName);
            }
        } else {
            throw new \Exception('Must provide "--topic", or "--subscription"');
        }
    });

if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
