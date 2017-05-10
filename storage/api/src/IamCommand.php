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
use InvalidArgumentException;

/**
 * Command line utility to manage Storage IAM.
 *
 * Usage: php storage.php iam
 */
class IamCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('iam')
            ->setDescription('Manage IAM for Storage')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Storage IAM policies.

    <info>php %command.full_name% my-bucket</info>

    <info>php %command.full_name% my-bucket --role my-role --add-member user/test@email.com</info>

    <info>php %command.full_name% my-bucket --role my-role --remove-member user/test@email.com</info>

EOF
            )
            ->addArgument(
                'bucket',
                InputArgument::REQUIRED,
                'The bucket that you want to change IAM for. '
            )
            ->addOption(
                'role',
                null,
                InputOption::VALUE_REQUIRED,
                'The new role to add to a bucket. '
            )
            ->addOption(
                'add-member',
                null,
                InputOption::VALUE_REQUIRED,
                'The new member to add with the new role to the bucket. '
            )
            ->addOption(
                'remove-member',
                null,
                InputOption::VALUE_REQUIRED,
                'The member to remove from a role for a bucket. '
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bucketName = $input->getArgument('bucket');
        $role = $input->getOption('role');
        $addMember = $input->getOption('add-member');
        $removeMember = $input->getOption('remove-member');
        if ($addMember) {
            if (!$role) {
                throw new \InvalidArgumentException('Must provide role as an option.');
            }
            add_bucket_iam_member($bucketName, $role, $addMember);
        } elseif($removeMember) {
            if (!$role) {
                throw new \InvalidArgumentException('Must provide role as an option.');
            }
            remove_bucket_iam_member($bucketName, $role, $removeMember);
        } else {
            view_bucket_iam_members($bucketName);
        }
    }
}
