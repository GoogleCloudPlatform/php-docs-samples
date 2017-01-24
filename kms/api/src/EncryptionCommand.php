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

namespace Google\Cloud\Samples\Kms;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to manage KMS encryption.
 *
 * Usage: php kms.php encryption
 */
class EncryptionCommand extends Command
{
    use KmsCommandTrait;

    protected function configure()
    {
        $this
            ->setName('encryption')
            ->setDescription('Manage encryption for KMS')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command uses the KMS API to encrypt and decrypt text in files.

Encrypt the text of a file using the specified CryptoKey:

    <info>php %command.full_name% my-keyring my-cryptokey file.txt file.txt.encrypted</info>

Decrypt the text of a file using the specified CryptoKey:

    <info>php %command.full_name% my-keyring my-cryptokey file.txt.encrypted file.txt.decrypted --decrypt</info>

EOF
            )
            ->addArgument(
                'keyring',
                InputArgument::REQUIRED,
                'The name of the keyring.'
            )
            ->addArgument(
                'cryptokey',
                InputArgument::REQUIRED,
                'The name of the cryptokey.'
            )
            ->addArgument(
                'infile',
                InputArgument::REQUIRED,
                'The target file.'
            )
            ->addArgument(
                'outfile',
                InputArgument::REQUIRED,
                'The file to store the result.'
            )
            ->addOption(
                'decrypt',
                null,
                InputOption::VALUE_NONE,
                'Performs the decrypt function instead of encrypt. '
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'The Google Cloud Platform project name to use for this invocation. ' .
                'If omitted then the current gcloud project is assumed. '
            )
            ->addOption(
                'location',
                null,
                InputOption::VALUE_REQUIRED,
                'The location of the cryptokey or keyring.',
                'global'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$projectId = $input->getOption('project')) {
            $projectId = $this->getProjectIdFromGcloud();
        }
        $keyRing = $input->getArgument('keyring');
        $cryptoKey = $input->getArgument('cryptokey');
        $infile = $input->getArgument('infile');
        $outfile = $input->getArgument('outfile');
        $location = $input->getOption('location');
        if (!$input->getOption('decrypt')) {
            encrypt($projectId, $keyRing, $cryptoKey, $infile, $outfile, $location);
        } else {
            decrypt($projectId, $keyRing, $cryptoKey, $infile, $outfile, $location);
        }
    }
}
