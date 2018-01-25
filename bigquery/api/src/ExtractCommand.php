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
 * Command line utility to import data into BigQuery.
 *
 * Usage: php bigquery.php extract
 */
class ExtractCommand extends Command
{
    use ProjectIdTrait;

    protected function configure()
    {
        $this
            ->setName('extract')
            ->setDescription('Extract data from a BigQuery table into a Cloud Storage bucket')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command extracts your data from BigQuery into
Google Cloud Storage.

Extract a CSV file

    <info>php %command.full_name% DATASET.TABLE gs://my_bucket/my_object</info>

Extract a JSON file

    <info>php %command.full_name% DATASET.TABLE gs://my_bucket/my_object --format=JSON</info>

EOF
            )
            ->addArgument(
                'dataset.table',
                InputArgument::REQUIRED,
                'The destination table for the import'
            )
            ->addArgument(
                'destination',
                InputArgument::REQUIRED,
                'The fully walified path to a Google Cloud Storage location. ' .
                'e.g. gs://mybucket/myfolder/'
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'The Google Cloud Platform project name to use for this invocation. ' .
                'If omitted then the current gcloud project is assumed. '
            )->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'The format to extract in. One of "csv", "json", or "avro".',
                'csv'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$projectId = $input->getOption('project')) {
            $projectId = $this->getProjectIdFromGcloud();
        }
        $fullTableName = $input->getArgument('dataset.table');
        if (1 !== substr_count($fullTableName, '.')) {
            throw new InvalidArgumentException('Table must in the format "dataset.table"');
        }
        list($datasetId, $tableId) = explode('.', $fullTableName);
        $bigQuery = new BigQueryClient([
            'projectId' => $projectId,
        ]);
        $dataset = $bigQuery->dataset($datasetId);
        $table = $dataset->table($tableId);
        $destination = $input->getArgument('destination');
        if (!$dataset->exists()) {
            throw new InvalidArgumentException('The supplied dataset does not exist for this project');
        }
        if (!$table->exists()) {
            throw new InvalidArgumentException('The supplied table does not exist for this project. ');
        }
        $message = sprintf('<info>extracting table for project %s</info>', $projectId);
        $output->writeln($message);

        if (0 !== strpos($destination, 'gs://')) {
            throw new InvalidArgumentException('Destination must start with "gs://" for Cloud Storage');
        }
        $destination = substr($destination, 5);
        if (false === strpos($destination, '/')) {
            throw new InvalidArgumentException('Destination does not contain object name');
        }
        list($bucketName, $objectName) = explode('/', $destination, 2);
        $format = strtoupper($input->getOption('format'));
        if ($format === 'JSON') {
            $format = 'NEWLINE_DELIMITED_JSON';
        }
        if (!in_array($format, ['CSV', 'NEWLINE_DELIMITED_JSON', 'AVRO'])) {
            throw new InvalidArgumentException('Invalid format');
        }

        extract_table($projectId, $datasetId, $tableId, $bucketName, $objectName, $format);
    }
}
