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
 * Command line utility to manage Cloud Storage bucket labels.
 *
 * Usage: php storage.php bucket-labels
 */
class BucketLabelsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('bucket-labels')
            ->setDescription('Manage Cloud Storage bucket labels')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage Bucket labels.

    <info>php %command.full_name% --help</info>

EOF
            )
            ->addArgument(
                'bucket',
                InputArgument::REQUIRED,
                'The Cloud Storage bucket name'
            )
            ->addArgument(
                'label',
                InputArgument::OPTIONAL,
                'The Cloud Storage label'
            )
            ->addOption(
                'value',
                null,
                InputOption::VALUE_REQUIRED,
                'Set the value of the label'
            )
            ->addOption(
                'remove',
                null,
                InputOption::VALUE_NONE,
                'Remove the buckets label'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bucketName = $input->getArgument('bucket');
        if ($label = $input->getArgument('label')) {
            if ($value = $input->getOption('value')) {
                add_bucket_label($bucketName, $label, $value);
            } elseif ($input->getOption('remove')) {
                remove_bucket_label($bucketName, $label);
            } else {
                throw new \Exception('You must provide --value or --remove '
                    . 'when including a label name.');
            }
        } else {
            get_bucket_labels($bucketName);
        }
    }
}
