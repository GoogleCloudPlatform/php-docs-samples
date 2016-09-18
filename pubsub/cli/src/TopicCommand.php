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
 * Command line utility to manage Pub/Sub topics.
 *
 * Usage: php pubsub.php topic
 */
class TopicCommand extends Command
{
    use ProjectIdTrait;

    protected function configure()
    {
        $this
            ->setName('topic')
            ->setDescription('Manage topics for Pub\Sub')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Pub\Sub topics.

    <info>php %command.full_name%</info>

EOF
            )
            ->addArgument(
                'topic',
                InputArgument::OPTIONAL,
                'The topic name'
            )
            ->addArgument(
                'message',
                InputArgument::OPTIONAL,
                'A message to publish to the topic'
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
                'Create the topic. '
            )
            ->addOption(
                'delete',
                null,
                InputOption::VALUE_NONE,
                'Delete the topic. '
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$projectId = $input->getOption('project')) {
            $projectId = $this->getProjectIdFromGcloud();
        }
        $topicName = $input->getArgument('topic');
        if (empty($topicName)) {
            list_topics($projectId);
        } elseif ($input->getOption('create')) {
            create_topic($projectId, $topicName);
        } elseif ($input->getOption('delete')) {
            delete_topic($projectId, $topicName);
        } elseif ($message = $input->getArgument('message')) {
            publish_message($projectId, $topicName, $message);
        } else {
            throw new \Exception('Must provide "--create", "--delete" or "message" with topic name');
        }
    }
}
