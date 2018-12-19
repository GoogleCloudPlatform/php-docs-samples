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

namespace Google\Cloud\Samples\Monitoring;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Stackdriver Monitoring');

$inputDefinition = new InputDefinition([
    new InputArgument('project_id', InputArgument::REQUIRED, 'The project id'),
]);

$application->add(new Command('create-metric'))
    ->setDefinition($inputDefinition)
    ->setDescription('Creates a logging metric.')
    ->setCode(function ($input, $output) {
        create_metric(
            $input->getArgument('project_id')
        );
    });

$application->add(new Command('create-uptime-check'))
    ->setDefinition(clone $inputDefinition)
    ->addArgument('host_name', InputArgument::OPTIONAL, 'The uptime check host name', 'example.com')
    ->addArgument('display_name', InputArgument::OPTIONAL, 'The uptime check display name', 'New uptime check')
    ->setDescription('Creates an uptime check.')
    ->setCode(function ($input, $output) {
        create_uptime_check(
            $input->getArgument('project_id'),
            $input->getArgument('host_name'),
            $input->getArgument('display_name')
        );
    });

$application->add(new Command('delete-metric'))
    ->setDefinition(clone $inputDefinition)
    ->addArgument('metric_id', InputArgument::REQUIRED, 'The metric descriptor id')
    ->setDescription('Deletes a logging metric.')
    ->setCode(function ($input, $output) {
        delete_metric(
            $input->getArgument('project_id'),
            $input->getArgument('metric_id')
        );
    });

$application->add(new Command('delete-uptime-check'))
    ->setDefinition(clone $inputDefinition)
    ->addArgument('config_name', InputArgument::REQUIRED, 'The uptime check config name')
    ->setDescription('Deletes an uptime check config.')
    ->setCode(function ($input, $output) {
        delete_uptime_check(
            $input->getArgument('project_id'),
            $input->getArgument('config_name')
        );
    });


$application->add(new Command('get-descriptor'))
    ->setDefinition(clone $inputDefinition)
    ->addArgument('metric_id', InputArgument::REQUIRED, 'The metric descriptor id')
    ->setDescription('Gets a logging descriptor.')
    ->setCode(function ($input, $output) {
        get_descriptor(
            $input->getArgument('project_id'),
            $input->getArgument('metric_id')
        );
    });

$application->add(new Command('get-resource'))
    ->setDefinition(clone $inputDefinition)
    ->addArgument('resource_type', InputArgument::REQUIRED, 'The monitored resource type')
    ->setDescription('Gets a monitored resource.')
    ->setCode(function ($input, $output) {
        get_resource(
            $input->getArgument('project_id'),
            $input->getArgument('resource_type')
        );
    });

$application->add(new Command('get-uptime-check'))
    ->setDefinition(clone $inputDefinition)
    ->addArgument('config_name', InputArgument::REQUIRED, 'The uptime check config name')
    ->setDescription('Gets an uptime check config.')
    ->setCode(function ($input, $output) {
        get_uptime_check(
            $input->getArgument('project_id'),
            $input->getArgument('config_name')
        );
    });

$application->add(new Command('list-descriptors'))
    ->setDefinition($inputDefinition)
    ->setDescription('Lists logging descriptors.')
    ->setCode(function ($input, $output) {
        list_descriptors(
            $input->getArgument('project_id')
        );
    });

$application->add(new Command('list-resources'))
    ->setDefinition(clone $inputDefinition)
    ->setDescription('List monitored resources.')
    ->setCode(function ($input, $output) {
        list_resources(
            $input->getArgument('project_id')
        );
    });

$application->add(new Command('list-uptime-check-ips'))
    ->setDefinition($inputDefinition)
    ->setDescription('Lists Uptime Check IPs.')
    ->setCode(function ($input, $output) {
        list_uptime_check_ips(
            $input->getArgument('project_id')
        );
    });

$application->add(new Command('list-uptime-checks'))
    ->setDefinition($inputDefinition)
    ->setDescription('Lists Uptime Check Configs.')
    ->setCode(function ($input, $output) {
        list_uptime_checks(
            $input->getArgument('project_id')
        );
    });

$application->add(new Command('read-timeseries-align'))
    ->setDefinition(clone $inputDefinition)
    ->addOption('minutes-ago', null, InputOption::VALUE_REQUIRED, 20,
        'How many minutes in the past to start the time series.')
    ->setDescription('Aggregates metrics for each timeseries.')
    ->setCode(function ($input, $output) {
        read_timeseries_align(
            $input->getArgument('project_id'),
            $input->getOption('minutes-ago')
        );
    });

$application->add(new Command('read-timeseries-fields'))
    ->setDefinition(clone $inputDefinition)
    ->addOption('minutes-ago', null, InputOption::VALUE_REQUIRED, 20,
        'How many minutes in the past to start the time series.')
    ->setDescription('Reads Timeseries fields.')
    ->setCode(function ($input, $output) {
        read_timeseries_fields(
            $input->getArgument('project_id'),
            $input->getOption('minutes-ago')
        );
    });

$application->add(new Command('read-timeseries-reduce'))
    ->setDefinition(clone $inputDefinition)
    ->addOption('minutes-ago', null, InputOption::VALUE_REQUIRED, 20,
        'How many minutes in the past to start the time series.')
    ->setDescription('Aggregates metrics across multiple timeseries.')
    ->setCode(function ($input, $output) {
        read_timeseries_reduce(
            $input->getArgument('project_id'),
            $input->getOption('minutes-ago')
        );
    });

$application->add(new Command('read-timeseries-simple'))
    ->setDefinition(clone $inputDefinition)
    ->addOption('minutes-ago', null, InputOption::VALUE_REQUIRED, 20,
        'How many minutes in the past to start the time series.')
    ->setDescription('Reads a timeseries.')
    ->setCode(function ($input, $output) {
        read_timeseries_simple(
            $input->getArgument('project_id'),
            $input->getOption('minutes-ago')
        );
    });

$application->add(new Command('write-timeseries'))
    ->setDefinition($inputDefinition)
    ->setDescription('Writes a timeseries.')
    ->setCode(function ($input, $output) {
        write_timeseries(
            $input->getArgument('project_id')
        );
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
