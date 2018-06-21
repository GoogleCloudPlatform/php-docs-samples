<?php
/**
 * Copyright 2018 Google Inc.
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

namespace Google\Cloud\Samples\Monitoring;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Stackdriver Monitoring Alerts');

$inputDefinition = new InputDefinition([
    new InputArgument('project_id', InputArgument::REQUIRED, 'The project id'),
]);

$application->add(new Command('backup-policies'))
    ->setDefinition($inputDefinition)
    ->setDescription('Back up alert policies.')
    ->setCode(function ($input, $output) {
        alert_backup_policies(
            $input->getArgument('project_id')
        );
    });

$application->add(new Command('create-channel'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create a notification channel.')
    ->setCode(function ($input, $output) {
        alert_create_channel(
            $input->getArgument('project_id')
        );
    });

$application->add(new Command('create-policy'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create an alert policy.')
    ->setCode(function ($input, $output) {
        alert_create_policy(
            $input->getArgument('project_id')
        );
    });

$application->add(new Command('delete-channel'))
    ->setDefinition(clone $inputDefinition)
    ->addArgument('channel_id', InputArgument::REQUIRED, 'The notification channel ID to delete')
    ->setDescription('Delete a notification channel.')
    ->setCode(function ($input, $output) {
        alert_delete_channel(
            $input->getArgument('project_id'),
            $input->getArgument('channel_id')
        );
    });

$application->add(new Command('enable-policies'))
    ->setDefinition(clone $inputDefinition)
    ->addArgument('enable', InputArgument::OPTIONAL, 'Enable or disable the polcies', true)
    ->addArgument('filter', InputArgument::OPTIONAL, 'Only enable/disable alert policies that match a filter.')
    ->setDescription('Enable or disable alert policies in a project.')
    ->setCode(function ($input, $output) {
        alert_enable_policies(
            $input->getArgument('project_id'),
            $input->getArgument('enable'),
            $input->getArgument('filter')
        );
    });

$application->add(new Command('restore-policies'))
    ->setDefinition($inputDefinition)
    ->setDescription('Restore alert policies from a backup.')
    ->setCode(function ($input, $output) {
        alert_restore_policies(
            $input->getArgument('project_id')
        );
    });

$application->add(new Command('list-policies'))
    ->setDefinition($inputDefinition)
    ->setDescription('List alert policies.')
    ->setCode(function ($input, $output) {
        alert_list_policies(
            $input->getArgument('project_id')
        );
    });

$application->add(new Command('list-channels'))
    ->setDefinition($inputDefinition)
    ->setDescription('List alert channels.')
    ->setCode(function ($input, $output) {
        alert_list_channels(
            $input->getArgument('project_id')
        );
    });
$application->add(new Command('replace-channels'))
    ->setDefinition(clone $inputDefinition)
    ->addArgument('policy_id', InputArgument::REQUIRED, 'The policy id')
    ->addArgument('channel_id', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'list of channel ids')
    ->setDescription('Replace alert channels.')
    ->setCode(function ($input, $output) {
        alert_replace_channels(
            $input->getArgument('project_id'),
            $input->getArgument('policy_id'),
            (array) $input->getArgument('channel_id')
        );
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
