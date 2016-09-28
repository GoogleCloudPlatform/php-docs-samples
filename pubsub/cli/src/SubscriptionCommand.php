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

namespace Google\Cloud\Samples\PubSub;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to manage Pub/Sub subscriptions.
 *
 * Usage: php pubsub.php subscription
 */
class SubscriptionCommand extends Command
{
    use ProjectIdTrait;

    protected function configure()
    {
        $this
            ->setName('subscription')
            ->setDescription('Manage subscriptions for Pub\Sub')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Pub\Sub subscriptions.

    <info>php %command.full_name%</info>

EOF
            )
            ->addArgument(
                'subscription',
                InputArgument::OPTIONAL,
                'The subscription name'
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'The Google Cloud Platform project name to use for this invocation. ' .
                'If omitted then the current gcloud project is assumed. '
            )
            ->addOption(
                'create',
                null,
                InputOption::VALUE_NONE,
                'Create the subscription. '
            )
            ->addOption(
                'topic',
                null,
                InputOption::VALUE_REQUIRED,
                'The topic for the subscription (when using --create). '
            )
            ->addOption(
                'endpoint',
                null,
                InputOption::VALUE_REQUIRED,
                'An optional endpoint for push subscriptions.'
            )
            ->addOption(
                'delete',
                null,
                InputOption::VALUE_NONE,
                'Delete the subscription. '
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$projectId = $input->getOption('project')) {
            $projectId = $this->getProjectIdFromGcloud();
        }
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
    }
}
