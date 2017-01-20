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

namespace Google\Cloud\Samples\Kms;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to manage KMS IAM.
 *
 * Usage: php kms.php iam
 */
class IamCommand extends Command
{
    use KmsCommandTrait;

    protected function configure()
    {
        $this
            ->setName('iam')
            ->setDescription('Manage IAM for KMS')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages KMS IAM policies.

    <info>php %command.full_name% my-keyring</info>

    <info>php %command.full_name% my-keyring my-cryptokey</info>

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
                'service-account-email',
                null,
                InputOption::VALUE_REQUIRED,
                'The service accunt email to add to the policy.'
            )
            ->addOption(
                'user-email',
                null,
                InputOption::VALUE_REQUIRED,
                'The user email to add to the policy.'
            )
            ->addOption(
                'role',
                null,
                InputOption::VALUE_REQUIRED,
                'The role of the policy.',
                'roles/cloudkms.cryptoKeyEncrypterDecrypter'
            )
            ->addOption(
                'location',
                null,
                InputOption::VALUE_REQUIRED,
                'The location of the cryptokey or keyring.',
                'global'
            )
            ->addOption(
                'remove',
                null,
                InputOption::VALUE_NONE,
                'If supplied, will remove the user or service account from the policy'
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
        $role = $input->getOption('role');
        $location = $input->getOption('location');
        $userEmail = $input->getOption('user-email');
        $serviceAccountEmail = $input->getOption('service-account-email');
        if ($cryptoKey) {
            if (empty($userEmail) && empty($serviceAccountEmail)) {
                get_cryptokey_policy($projectId, $keyRing, $cryptoKey, $location);
            } else {
                if ($userEmail) {
                    $member = 'user:' . $userEmail;
                } else {
                    $member = 'serviceAccount:' . $serviceAccountEmail;
                }
                if ($input->getOption('remove')) {
                    remove_member_from_cryptokey_policy($projectId, $keyRing, $cryptoKey, $member, $role, $location);
                } else {
                    add_member_to_cryptokey_policy($projectId, $keyRing, $cryptoKey, $member, $role, $location);
                }
            }
        } else {
            if (empty($userEmail) && empty($serviceAccountEmail)) {
                get_keyring_policy($projectId, $keyRing, $location);
            } else {
                if ($userEmail) {
                    $member = 'user:' . $userEmail;
                } else {
                    $member = 'serviceAccount:' . $serviceAccountEmail;
                }
                if ($input->getOption('remove')) {
                    remove_member_from_keyring_policy($projectId, $keyRing, $member, $role, $location);
                } else {
                    add_member_to_keyring_policy($projectId, $keyRing, $member, $role, $location);
                }
            }
        }
    }
}
