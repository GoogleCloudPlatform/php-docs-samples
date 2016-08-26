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
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Output\OutputInterface;
use InvalidArgumentException;

/**
 * Command line utility to list BigQuery tables.
 *
 * Usage: php bigquery.php tables
 */
class BrowseTableCommand extends Command
{
    use ProjectIdTrait;

    protected function configure()
    {
        $this
            ->setName('browse-table')
            ->setDescription('Browse a BigQuery table')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command outputs the rows of a BigQuery table.

    <info>php %command.full_name% DATASET.TABLE</info>

EOF
            )
            ->addArgument(
                'dataset.table',
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
            ->addOption(
                'max-results',
                null,
                InputOption::VALUE_REQUIRED,
                'The number of rows to return on each API call.',
                10
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$projectId = $input->getOption('project')) {
            $projectId = $this->detectProjectId();
        }
        $maxResults = $input->getOption('max-results');
        $fullTableName = $input->getArgument('dataset.table');
        if (1 !== substr_count($fullTableName, '.')) {
            throw new InvalidArgumentException('Table must in the format "dataset.table"');
        }
        list($datasetId, $tableId) = explode('.', $fullTableName);

        // create the function to determine if we should paginate
        $question = $this->getHelper('question');
        $q = new ConfirmationQuestion('[Press enter for next page, "n" to exit]');
        $shouldPaginate = function () use ($input, $output, $question, $q) {
            if (!$input->isInteractive()) {
                return false;
            }

            return $question->ask($input, $output, $q);
        };

        $totalRows = paginate_table($projectId, $datasetId, $tableId, $maxResults, $shouldPaginate);

        printf('Found %s row(s)' . PHP_EOL, $totalRows);
    }
}
