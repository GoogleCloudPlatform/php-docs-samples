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

namespace Google\Cloud\Samples\BigQuery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Google\Cloud\BigQuery\BigQueryClient;
use InvalidArgumentException;

/**
 * Command line utility to copy a BigQuery table.
 *
 * Usage: php bigquery.php copy-table
 */
class CopyTableCommand extends Command
{
    use ProjectIdTrait;

    protected function configure()
    {
        $this
            ->setName('copy-table')
            ->setDescription('Copy a BigQuery table into another BigQuery table')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command copies your data and schema from a BigQuery
table into another BigQuery Table.

    <info>php %command.full_name% DATASET SOURCE_TABLE DESTINATION_TABLE</info>


EOF
            )
            ->addArgument(
                'dataset',
                InputArgument::REQUIRED,
                'The dataset for the copy'
            )
            ->addArgument(
                'source-table',
                InputArgument::REQUIRED,
                'The BigQuery table to copy from'
            )
            ->addArgument(
                'destination-table',
                InputArgument::REQUIRED,
                'The BigQuery table to copy to'
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
        $sourceTableId = $input->getArgument('source-table');
        $bigQuery = new BigQueryClient([
            'projectId' => $projectId,
        ]);
        $dataset = $bigQuery->dataset($datasetId);
        $sourceTable = $dataset->table($sourceTableId);
        $destinationTableId = $input->getArgument('destination-table');
        if (!$dataset->exists()) {
            throw new InvalidArgumentException('The supplied dataset does not exist for this project');
        }
        if (!$sourceTable->exists()) {
            throw new InvalidArgumentException('The supplied source table does not exist for this project. ');
        }
        $message = sprintf('<info>Copying table for project %s</info>', $projectId);
        $output->writeln($message);

        copy_table($projectId, $datasetId, $sourceTableId, $destinationTableId);
    }
}
