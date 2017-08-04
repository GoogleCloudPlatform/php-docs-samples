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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Auth');

// Create auth-cloud-implicit Command.
$application->add((new Command('auth-cloud-implicit'))
    ->setDescription('Authenticate to a cloud client library using a service account implicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud client library
using a service account implicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        auth_cloud_implicit();
    })
);

// Create auth-cloud-explicit Command.
$application->add((new Command('auth-cloud-explicit'))
    ->setDescription('Authenticate to a cloud client library using a service account explicitly.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command authenticates to a cloud client library
using a service account explicitly.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        auth_cloud_explicit();
    })
);

if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
