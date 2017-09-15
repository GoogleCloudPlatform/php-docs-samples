<?php
/**
 * Copyright 2017 Google Inc.
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

namespace Google\Cloud\Samples\Storage;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to manage Cloud Storage requester pays buckets.
 *
 * Usage: php storage.php requester-pays
 */
class RequesterPaysCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('requester-pays')
            ->setDescription('Manage Cloud Storage requester pays buckets.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage requester pays buckets.

    <info>php %command.full_name% --help</info>

EOF
            )
            ->addArgument(
                'project',
                InputArgument::REQUIRED,
                'Your billable Google Cloud Project ID'
            )
            ->addArgument(
                'bucket',
                InputArgument::REQUIRED,
                'The Cloud Storage requester pays bucket name'
            )
            ->addArgument(
                'object',
                InputArgument::OPTIONAL,
                'The Cloud Storage requester pays object name'
            )
            ->addArgument(
                'download-to',
                null,
                InputArgument::OPTIONAL,
                'Path to store the dowloaded file'
            )
            ->addOption(
                'enable',
                null,
                InputOption::VALUE_NONE,
                'Enable requester pays on a Cloud Storage bucket'
            )
            ->addOption(
                'disable',
                null,
                InputOption::VALUE_NONE,
                'Disable requester pays on a Cloud Storage bucket'
            )
            ->addOption(
                'check-status',
                null,
                InputOption::VALUE_NONE,
                'Check requester pays status on a Cloud Storage bucekt'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectId = $input->getArgument('project');
        $bucketName = $input->getArgument('bucket');
        if ($objectName = $input->getArgument('object')) {
            if ($destination = $input->getArgument('download-to')) {
                download_file_requester_pays($projectId, $bucketName, $objectName, $destination);
            }
        } else if ($input->getOption('enable')) {
            enable_requester_pays($projectId, $bucketName);
        } else if ($input->getOption('disable')) {
            disable_requester_pays($projectId, $bucketName);
        } else if ($input->getOption('check-status')) {
           get_requester_pays_status($projectId, $bucketName);
        }
    }
}
