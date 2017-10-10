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

namespace Google\Cloud\Samples\Iap;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Iap');

// Create request Command.
$application->add((new Command('request'))
    ->addArgument('url', InputArgument::REQUIRED, 'The Identity-Aware Proxy-protected URL to fetch.')
    ->addArgument('clientId', InputArgument::REQUIRED, 'The client ID used by Identity-Aware Proxy.')
    ->addArgument('serviceAccountPath', InputArgument::REQUIRED, 'Path for the service account you want to use.')
    ->setDescription('Make a request to an IAP-protected resource using a service account.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command makes a request to an IAP-protected resource.
    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $response_body = make_iap_request($input->getArgument('url'), $input->getArgument('clientId'), $input->getArgument('serviceAccountPath'));
        printf($response_body . PHP_EOL);
    })
);

if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
