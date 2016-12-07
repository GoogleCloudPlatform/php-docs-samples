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

namespace Google\Cloud\Samples\Storage;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to encrypt Cloud Storage objects.
 *
 * Usage: php storage.php encryption
 */
class EncryptionCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('encryption')
            ->setDescription('Upload and download Cloud Storage objects with encryption')
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
                'key',
                null,
                InputOption::VALUE_REQUIRED,
                'Supply your encryption key'
            )
            ->addOption(
                'rotate-key',
                null,
                InputOption::VALUE_REQUIRED,
                'Supply a new encryption key'
            )
            ->addOption(
                'generate-key',
                null,
                InputOption::VALUE_NONE,
                'Generates an encryption key'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('generate-key')) {
            generate_encryption_key();
        } else {
            $bucketName = $input->getArgument('bucket');
            $objectName = $input->getArgument('object');
            $encryptionKey = $input->getOption('key');
            if ($bucketName && $objectName) {
                if ($source = $input->getOption('upload-from')) {
                    upload_encrypted_object($bucketName, $objectName, $source, $encryptionKey);
                } elseif ($destination = $input->getOption('download-to')) {
                    download_encrypted_object($bucketName, $objectName, $destination, $encryptionKey);
                } elseif ($rotateKey = $input->getOption('rotate-key')) {
                    if (is_null($encryptionKey)) {
                        throw new \Exception('--key is required when using --rotate-key');
                    }
                    rotate_encryption_key($bucketName, $objectName, $encryptionKey, $rotateKey);
                } else {
                    throw new \Exception('Supply --rotate-key, --upload-from or --download-to');
                }
            } else {
                throw new \Exception('Supply a bucket and object OR --generate-key');
            }
        }
    }
}
