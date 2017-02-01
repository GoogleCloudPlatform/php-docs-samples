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
 * Command line utility to manage KMS key versions.
 *
 * Usage: php kms.php version
 */
class VersionCommand extends Command
{
    use KmsCommandTrait;

    protected function configure()
    {
        $this
            ->setName('version')
            ->setDescription('Manage key versions for KMS')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages KMS key versions.

List all versions of a CryptoKey:

    <info>php %command.full_name% my-keyring my-cryptokey</info>

Display information about a specific CryptoKey version:

    <info>php %command.full_name% my-keyring my-cryptokey 1</info>

Create a new CryptoKey version:

    <info>php %command.full_name% my-keyring my-cryptokey --create</info>

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
                'version',
                InputArgument::OPTIONAL,
                'The version of the cryptokey.'
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
                'If supplied, will create the keyring, cryptokey, or cryptokey version'
            )
            ->addOption(
                'destroy',
                null,
                InputOption::VALUE_NONE,
                'If supplied, will destroy the cryptokey version'
            )
            ->addOption(
                'disable',
                null,
                InputOption::VALUE_NONE,
                'If supplied, will disable the cryptokey version'
            )
            ->addOption(
                'enable',
                null,
                InputOption::VALUE_NONE,
                'If supplied, will enable the cryptokey version'
            )
            ->addOption(
                'restore',
                null,
                InputOption::VALUE_NONE,
                'If supplied, will restore the cryptokey version'
            )
            ->addOption(
                'set-primary',
                null,
                InputOption::VALUE_NONE,
                'If supplied, will disable the cryptokey version'
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
        $cryptoKeyVersion = $input->getArgument('version');
        $location = $input->getOption('location');
        if ($input->getOption('create')) {
            create_cryptokey_version($projectId, $keyRing, $cryptoKey, $location);
        } elseif ($cryptoKeyVersion) {
            if ($input->getOption('destroy')) {
                destroy_cryptokey_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            } elseif ($input->getOption('disable')) {
                disable_cryptokey_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            } elseif ($input->getOption('restore')) {
                restore_cryptokey_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            } elseif ($input->getOption('enable')) {
                enable_cryptokey_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            } elseif ($input->getOption('set-primary')) {
                set_cryptokey_primary_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            } else {
                get_cryptokey_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            }
        } else {
            list_cryptokey_versions($projectId, $keyRing, $cryptoKey, $location);
        }
    }
}
