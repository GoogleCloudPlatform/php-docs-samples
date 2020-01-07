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

// Create Feed Default ACL command
$application->add(new Command('create-feed'))
    ->setDescription('Create real time feed.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command create real time feed.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('parent', InputArgument::REQUIRED, 'The parent of the feed')
    ->addArgument('feedId', InputArgument::REQUIRED, 'The Id of the feed')
    ->addArgument('topic', InputArgument::REQUIRED, 'The topic of the feed')
    ->addArgument('assetNames', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'The assets of which the feed will listen to')

    ->setCode(function ($input, $output) {
        $parent = $input->getArgument('parent');
        $feedId = $input->getArgument('feedId');
        $topic = $input->getArgument('topic');
        $assetNames = $input->getArgument('assetNames');
        create_feed($parent, $feedId, $topic, $assetNames);
    });

// Get Feed Default ACL command
$application->add(new Command('get-feed'))
    ->setDescription('Get real time feed.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command get real time feed.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('feedName', InputArgument::REQUIRED, 'The Name of the feed will be got')

    ->setCode(function ($input, $output) {
        $feedName = $input->getArgument('feedName');
        get_feed($feedName);
    });

// List Feeds Default ACL command
$application->add(new Command('list-feeds'))
    ->setDescription('List real time feed under a resource.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command list real time feeds.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('parent', InputArgument::REQUIRED, 'The resource parent of the feeds will be got')

    ->setCode(function ($input, $output) {
        $parent = $input->getArgument('parent');
        list_feeds($parent);
    });

// Update Feed Default ACL command
$application->add(new Command('update-feed'))
    ->setDescription('Update real time feed.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command update assetNames of a real time feed.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('feedName', InputArgument::REQUIRED, 'The Id of the feed')
    ->addArgument('assetNames', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'The assets of which the feed will listen to')

    ->setCode(function ($input, $output) {
        $feedId = $input->getArgument('feedId');
        $assetNames = $input->getArgument('assetNames');
        update_feed($feedId, $assetNames);
    });

// Delete Feed Default ACL command
$application->add(new Command('delete-feed'))
    ->setDescription('Delete real time feed.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command delete real time feed.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('feedName', InputArgument::REQUIRED, 'The Name of the feed to be deleted')

    ->setCode(function ($input, $output) {
        $feedName = $input->getArgument('feedName');
        delete_feed($feedName);
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
