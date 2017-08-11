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

namespace Google\Cloud\Samples\Auth;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Auth');

// Create auth-cloud-implicit Command.
$application->add((new Command('auth-cloud-implicit'))
    ->addArgument('projectId', InputArgument::REQUIRED, 'Your project ID')
    ->setDescription('Authenticate to a cloud client library using a service account implicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud client library
using a service account implicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        auth_cloud_implicit($input->getArgument('projectId'));
    })
);

// Create auth-cloud-explicit Command.
$application->add((new Command('auth-cloud-explicit'))
    ->addArgument('serviceAccountPath', InputArgument::REQUIRED, 'Path to your service account.')
    ->addArgument('projectId', InputArgument::REQUIRED, 'Your project ID')
    ->setDescription('Authenticate to a cloud client library using a service account explicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud client library
using a service account explicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        auth_cloud_explicit($input->getArgument('projectId'), $input->getArgument('serviceAccountPath'));
    })
);

// Create auth-cloud-explicit-compute-engine Command.
$application->add((new Command('auth-cloud-explicit-compute-engine'))
    ->addArgument('projectId', InputArgument::REQUIRED, 'Your project ID')
    ->setDescription('Authenticate to a cloud client library using Compute Engine credentials explicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud client library
using Compute Engine credentials explicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        auth_cloud_explicit_compute_engine($input->getArgument('projectId'));
    })
);

// Create auth-cloud-explicit-app-engine Command.
$application->add((new Command('auth-cloud-explicit-app-engine'))
    ->addArgument('projectId', InputArgument::REQUIRED, 'Your project ID')
    ->setDescription('Authenticate to a cloud client library using App Engine Standard credentials explicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud client library
using App Engine Standard credentials explicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        auth_cloud_explicit_app_engine($input->getArgument('projectId'));
    })
);

// Create auth-api-implicit Command.
$application->add((new Command('auth-api-implicit'))
    ->addArgument('projectId', InputArgument::REQUIRED, 'Your project ID')
    ->setDescription('Authenticate to a cloud API using a service account implicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud API using a
service account implicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        auth_api_implicit($input->getArgument('projectId'));
    })
);

// Create auth-api-explicit Command.
$application->add((new Command('auth-api-explicit'))
    ->addArgument('projectId', InputArgument::REQUIRED, 'Your project ID')
    ->addArgument('serviceAccountPath', InputArgument::REQUIRED, 'Path to your service account.')
    ->setDescription('Authenticate to a cloud API using a service account explicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud API using a
service account implicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('projectId');
        $serviceAccountPath = $input->getArgument('serviceAccountPath');
        auth_api_explicit($projectId, $serviceAccountPath);
    })
);

// Create auth-api-explicit-compute-engine Command.
$application->add((new Command('auth-api-explicit-compute-engine'))
    ->addArgument('projectId', InputArgument::REQUIRED, 'Your project ID')
    ->setDescription('Authenticate to a cloud API using Compute Engine credentials explicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud API using
Compute Engine credentials explicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('projectId');
        auth_api_explicit_compute_engine($projectId);
    })
);

// Create auth-api-explicit-app-engine Command.
$application->add((new Command('auth-api-explicit-app-engine'))
    ->addArgument('projectId', InputArgument::REQUIRED, 'Your project ID')
    ->setDescription('Authenticate to a cloud API using App Engine Standard credentials explicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud API using
Compute Engine credentials explicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('projectId');
        auth_api_explicit_compute_engine($projectId);
    })
);

// Create auth-http-implicit Command.
$application->add((new Command('auth-http-implicit'))
    ->addArgument('projectId', InputArgument::REQUIRED, 'Your project ID')
    ->setDescription('Authenticate to a cloud API with HTTP using a service account implicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud API with HTTP
using a service account implicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        auth_http_implicit($input->getArgument('projectId'));
    })
);

// Create auth-http-explicit Command.
$application->add((new Command('auth-http-explicit'))
    ->addArgument('projectId', InputArgument::REQUIRED, 'Your project ID')
    ->addArgument('serviceAccountPath', InputArgument::REQUIRED, 'Path to your service account.')
    ->setDescription('Authenticate to a cloud API with HTTP using a service account explicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud API with HTTP
using a service account explicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('projectId');
        $serviceAccountPath = $input->getArgument('serviceAccountPath');
        auth_http_explicit($projectId, $serviceAccountPath);
    })
);

if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
