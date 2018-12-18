<?php
/**
 * Copyright 2018 Google LLC
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

namespace Google\Cloud\Samples\Asset;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

$application = new Application();

// Create Bucket ACL command
$application->add(new Command('export'))
    ->setDescription('Export assets for given projec to specified path.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command exports assets for given project to specified path.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('project', InputArgument::REQUIRED, 'The project for which the assets will be exported')
    ->addArgument('filePath', InputArgument::REQUIRED, 'The path of file the assets will be exported to')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $dumpFilePath = $input->getArgument('filePath');
        export_assets($projectId, $dumpFilePath);
    });

// Create Bucket Default ACL command
$application->add(new Command('batch-get-history'))
    ->setDescription('Batch get the history of assets.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command batch gets history of assets.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('project', InputArgument::REQUIRED, 'The project for which assets history will be got')
    ->addArgument('assetNames', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'The assets of which the history will be got')

    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $assetNames = $input->getArgument('assetNames');
        batch_get_assets_history($projectId, $assetNames);
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
