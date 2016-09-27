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
 * Command line utility to manage Cloud Storage objects.
 *
 * Usage: php storage.php objects
 */
class ObjectsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('objects')
            ->setDescription('Manage Cloud Storage objects')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage objects.

    <info>php %command.full_name% --help</info>

EOF
            )
            ->addArgument(
                'bucket',
                InputArgument::REQUIRED,
                'The Cloud Storage bucket name'
            )
            ->addArgument(
                'object',
                InputArgument::OPTIONAL,
                'The Cloud Storage object name'
            )
            ->addOption(
                'upload-from',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the file to upload'
            )
            ->addOption(
                'download-to',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to store the dowloaded file'
            )
            ->addOption(
                'move-to',
                null,
                InputOption::VALUE_REQUIRED,
                'new name for the object'
            )
            ->addOption(
                'copy-to',
                null,
                InputOption::VALUE_REQUIRED,
                'copy path for the object'
            )
            ->addOption(
                'make-public',
                null,
                InputOption::VALUE_REQUIRED,
                'makes the supplied object public'
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
        $bucketName = $input->getArgument('bucket');
        if ($objectName = $input->getArgument('object')) {
            if ($source = $input->getOption('upload-from')) {
                upload_object($bucketName, $objectName, $source);
            } elseif ($destination = $input->getOption('download-to')) {
                download_object($bucketName, $objectName, $destination);
            } elseif ($newObjectName = $input->getOption('move-to')) {
                move_object($bucketName, $objectName, $bucketName, $newObjectName);
            } elseif ($newObjectName = $input->getOption('copy-to')) {
                copy_object($bucketName, $objectName, $bucketName, $newObjectName);
            } elseif ($input->getOption('make-public')) {
                make_public($bucketName, $objectName);
            } elseif ($input->getOption('delete')) {
                delete_object($bucketName, $objectName);
            } else {
                object_metadata($bucketName, $objectName);
            }
        } else {
            list_objects($bucketName);
        }
    }
}
