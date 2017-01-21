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
 * Usage: php kms.php keyring
 */
class KeyRingCommand extends Command
{
    use KmsCommandTrait;

    protected function configure()
    {
        $this
            ->setName('keyring')
            ->setDescription('Manage keyrings for KMS')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages KMS keyrings.

    <info>php %command.full_name%</info>

    <info>php %command.full_name% my-keyring</info>

    <info>php %command.full_name% my-keyring --create</info>

EOF
            )
            ->addArgument(
                'keyring',
                InputArgument::OPTIONAL,
                'The name of the keyring.'
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
        $ring = $input->getArgument('keyring');
        $location = $input->getOption('location');
        if ($ring) {
            if ($input->getOption('create')) {
                create_keyring($projectId, $ring, $location);
            } else {
                $keyRing = $this->getKeyRing($projectId, $ring, $location);
                $this->printKeyRing($keyRing);
            }
        } else {
            foreach ($this->getKeyRings($projectId, $location) as $keyRing) {
                $this->printKeyRing($keyRing);
                print(PHP_EOL);
            }
        }
    }

    private function getKeyRing($projectId, $keyRing, $location = 'global')
    {
        // The resource name of the keyring.
        $parent = sprintf('projects/%s/locations/%s/keyRings/%s',
            $projectId,
            $location,
            $keyRing
        );

        // Get the key ring.
        $kms = $this->getKmsClient();
        return $kms->projects_locations_keyRings->get($parent);
    }

    private function getKeyRings($projectId, $location = 'global')
    {
        // The resource name of the cryptokey version.
        $parent = sprintf('projects/%s/locations/%s',
            $projectId,
            $location
        );

        // Get the crypto key version.
        $kms = $this->getKmsClient();
        return $kms->projects_locations_keyRings
            ->listProjectsLocationsKeyRings($parent);
    }

    private function printKeyRing($keyRing)
    {
        // print the key ring.
        printf('Name: %s' . PHP_EOL, $keyRing->getName());
        printf('Create time: %s' . PHP_EOL, $keyRing->getCreateTime());
    }
}
