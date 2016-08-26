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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Google\Cloud\ServiceBuilder;
use InvalidArgumentException;
use Exception;

/**
 * Command line utility to import data into BigQuery.
 *
 * Usage: php bigquery.php import
 */
class ImportCommand extends Command
{
    use ProjectIdTrait;

    protected function configure()
    {
        $this
            ->setName('import')
            ->setDescription('Import data into a BigQuery table')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command imports your data into BigQuery from
a file, Datastore, or Cloud Storage.

Import a JSON file

    <info>php %command.full_name% DATASET.TABLE /path/to/my_data.json</info>

Import from Google Cloud Storage

    <info>php %command.full_name% DATASET.TABLE gs://my_bucket/my_data.csv</info>

Import from Google Datastore

    <info>php %command.full_name% DATASET.TABLE gs://my_bucket/datastore_entity.backup_info</info>

Stream data into BigQuery

    <info>php %command.full_name% DATASET.TABLE</info>

EOF
            )
            ->addArgument(
                'dataset.table',
                InputArgument::REQUIRED,
                'The destination table for the import'
            )
            ->addArgument(
                'source',
                InputArgument::OPTIONAL,
                'The filepath, datastore key, or GCS object path to use.'
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
        $question = $this->getHelper('question');
        if (!$projectId = $input->getOption('project')) {
            $projectId = $this->getProjectIdFromGcloud();
        }
        $message = sprintf('<info>Using project %s</info>', $projectId);
        $output->writeln($message);
        $fullTableName = $input->getArgument('dataset.table');
        if (1 !== substr_count($fullTableName, '.')) {
            throw new InvalidArgumentException('Table must in the format "dataset.table"');
        }
        list($datasetId, $tableId) = explode('.', $fullTableName);
        $builder = new ServiceBuilder([
            'projectId' => $projectId,
        ]);
        $bigQuery = $builder->bigQuery();
        $dataset = $bigQuery->dataset($datasetId);
        $table = $dataset->table($tableId);
        $source = $input->getArgument('source');
        $isDatastoreBackup = '.backup_info' === substr($source, -12);
        if (!$dataset->exists()) {
            throw new InvalidArgumentException('The supplied dataset does not exist for this project');
        }
        if (!$isDatastoreBackup) {
            if (!$table->exists()) {
                throw new InvalidArgumentException('The supplied table does not exist for this project. ' .
                    'Create a schema in the UI or use the "schema" command');
            }
        }

        if (empty($source)) {
            $info = $table->info();
            $data = $this->getRowData($info['schema']['fields'], $question, $input, $output);
            stream_row($projectId, $datasetId, $tableId, $data);
        } elseif (0 === strpos($source, 'gs://')) {
            $source = substr($source, 5);
            if (false === strpos($source, '/')) {
                throw new InvalidArgumentException('Source does not contain object name');
            }
            list($bucketName, $objectName) = explode('/', $source, 2);
            import_from_storage($projectId, $datasetId, $tableId, $bucketName, $objectName);
        } else {
            if (!(file_exists($source) && is_readable($source))) {
                throw new InvalidArgumentException('Source file does not exist or is not readable');
            }
            import_from_file($projectId, $datasetId, $tableId, $source);
        }
    }

    private function getRowData($fields, $question, $input, $output)
    {
        $data = [];
        foreach ($fields as $field) {
            if ($field['type'] === 'RECORD') {
                throw new Exception('Field type RECORD not supported for streaming. Use JSON or Datastore');
            }
            $required = $field['mode'] === 'REQUIRED';
            $repeated = $askAgain = $field['mode'] === 'REPEATED';
            $q = new Question(sprintf('%s%s: ', $field['name'], $required ? ' (required)' : ''));
            $answers = [];
            do {
                if ($answer = $question->ask($input, $output, $q)) {
                    $answers[] = $answer;
                } else {
                    $askAgain = false;
                }
            } while ($askAgain);
            $data[$field['name']] = $repeated ? $answers : array_shift($answers);
        }

        return $data;
    }
}
