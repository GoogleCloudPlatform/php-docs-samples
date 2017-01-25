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
 * Command line utility to manage KMS keys and keyrings.
 *
 * Usage: php kms.php key
 */
class KeyCommand extends Command
{
    use KmsCommandTrait;

    protected function configure()
    {
        $this
            ->setName('key')
            ->setDescription('Manage keys for KMS')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages KMS keys.

List all CrytoKeys for the supplied KeyRing:

    <info>php %command.full_name% my-keyring</info>

Display information about a CrytoKey:

    <info>php %command.full_name% my-keyring my-cryptokey</info>

Create a CrytoKey:

    <info>php %command.full_name% my-keyring new-cryptokey --create</info>

EOF
            )
            ->addArgument(
                'keyring',
                InputArgument::REQUIRED,
                'The name of the keyring.'
            )
            ->addArgument(
                'cryptokey',
                InputArgument::OPTIONAL,
                'The name of the cryptokey.'
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
        $key = $input->getArgument('cryptokey');
        $location = $input->getOption('location');

        if ($key) {
            if ($input->getOption('create')) {
                create_cryptokey($projectId, $keyRing, $key, $location);
            } else {
                $cryptoKey = $this->getCryptoKey($projectId, $keyRing, $key, $location);
                $this->printCryptoKey($cryptoKey);
            }
        } else {
            foreach ($this->getCryptoKeys($projectId, $keyRing, $location) as $cryptoKey) {
                $this->printCryptoKey($cryptoKey);
                print(PHP_EOL);
            }
        }
    }

    private function getCryptoKey($projectId, $keyRing, $key, $location = 'global')
    {
        // The resource name of the cryptokey.
        $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s',
            $projectId,
            $location,
            $keyRing,
            $key
        );

        // Get the crypto key.
        $kms = $this->getKmsClient();
        return $kms->projects_locations_keyRings_cryptoKeys->get($parent);
    }

    private function getCryptoKeys($projectId, $keyRing, $location = 'global')
    {
        // The resource name of the cryptokey version.
        $parent = sprintf('projects/%s/locations/%s/keyRings/%s',
            $projectId,
            $location,
            $keyRing
        );

        // Get the crypto key version.
        $kms = $this->getKmsClient();
        return $kms->projects_locations_keyRings_cryptoKeys
            ->listProjectsLocationsKeyRingsCryptoKeys($parent);
    }

    private function printCryptoKey($cryptoKey)
    {
        // print the crypto key
        printf('Name: %s' . PHP_EOL, $cryptoKey->getName());
        printf('Create Time: %s' . PHP_EOL, $cryptoKey->getCreateTime());
        printf('Purpose: %s' . PHP_EOL, $cryptoKey->getPurpose());
        printf('Primary Version: %s' . PHP_EOL, $cryptoKey->getPrimary()->getName());
        printf('Rotation Period: %s' . PHP_EOL, $cryptoKey->getRotationPeriod());
    }
}
