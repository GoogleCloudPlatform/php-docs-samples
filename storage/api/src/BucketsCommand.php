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

namespace Google\Cloud\Samples\Storage;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to manage Cloud Storage buckets.
 *
 * Usage: php storage.php buckets
 */
class BucketsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('buckets')
            ->setDescription('Manage buckets for Cloud Storage objects')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage ACL.

    <info>php %command.full_name% --help</info>

EOF
            )
            ->addArgument(
                'bucket',
                InputArgument::OPTIONAL,
                'The Cloud Storage bucket name'
            )
            ->addOption(
                'create',
                null,
                InputOption::VALUE_NONE,
                'Create the bucket'
            )
            ->addOption(
                'delete',
                null,
                InputOption::VALUE_NONE,
                'Delete the bucket'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($bucketName = $input->getArgument('bucket')) {
            if ($input->getOption('create')) {
                create_bucket($bucketName);
            } elseif ($input->getOption('delete')) {
                delete_bucket($bucketName);
            } else {
                throw new \Exception('Supply --create or --delete with bucket name');
            }
        } else {
            list_buckets();
        }
    }
}
