<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\BigQuery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to list BigQuery tables.
 *
 * Usage: php bigquery.php tables
 */
class TablesCommand extends Command
{
    use ProjectIdTrait;

    protected function configure()
    {
        $this
            ->setName('tables')
            ->setDescription('List BigQuery tables')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command lists all the tables associated with BigQuery.

    <info>php %command.full_name% DATASET</info>

EOF
            )
            ->addArgument(
                'dataset',
                InputArgument::REQUIRED,
                'The dataset to list tables'
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'The Google Cloud Platform project name to use for this invocation. ' .
                'If omitted then the current gcloud project is assumed. '
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$projectId = $input->getOption('project')) {
            $projectId = $this->getProjectIdFromGcloud();
        }
        $datasetId = $input->getArgument('dataset');

        list_tables($projectId, $datasetId);
    }
}
