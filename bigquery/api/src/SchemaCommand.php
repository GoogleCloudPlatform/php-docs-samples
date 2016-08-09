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
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Exception\BadRequestException;
use InvalidArgumentException;

/**
 * Command line utility to create a BigQuery schema.
 */
class SchemaCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('schema')
            ->setDescription('Create or delete a table schema in BigQuery')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command is an interactive tool for creating a BigQuery table
and defining a schema.

    <info>php %command.full_name% DATASET_ID</info>

EOF
            )
            ->addArgument(
                'dataset.table',
                InputArgument::REQUIRED,
                'The table to be created or deleted'
            )
            ->addArgument(
                'schema-json',
                InputArgument::OPTIONAL,
                'A file containing a JSON schema for the table'
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'The Google Cloud Platform project name to use for this invocation. ' .
                'If omitted then the current gcloud project is assumed. '
            )
            ->addOption(
                'delete',
                null,
                InputOption::VALUE_NONE,
                'Provide this option without a "schema-json" argument to delete the BigQuery table'
            )
            ->addOption(
                'no-confirmation',
                null,
                InputOption::VALUE_NONE,
                'If set, this utility will not prompt when deleting a table with "--delete"'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $question = $this->getHelper('question');
        if (!$projectId = $input->getOption('project')) {
            if (!$projectId = $this->getProjectIdFromGcloud()) {
                throw new \Exception('Could not derive a project ID from gloud. ' .
                    'You must supply a project ID using --project');
            }
        }
        $message = sprintf('<info>Using project %s</info>', $projectId);
        $output->writeln($message);

        $fullTableName = $input->getArgument('dataset.table');
        if (1 !== substr_count($fullTableName, '.')) {
            throw new InvalidArgumentException('Table must in the format "dataset.table"');
        }
        list($datasetId, $tableId) = explode('.', $fullTableName);
        $bigQuery = new BigQueryClient([
            'projectId' => $projectId
        ]);

        $dataset = $bigQuery->dataset($datasetId);
        if (!$dataset->exists()) {
            $message = sprintf('Dataset %s does not exist. Create it? [y/n]: ', $datasetId);
            $q = new ConfirmationQuestion($message);
            if (!$question->ask($input, $output, $q)) {
                return $output->writeln('<error>Task cancelled by user.</error>');
            }
            $dataset = $bigQuery->createDataset($datasetId);
        }

        if ($file = $input->getArgument('schema-json')) {
            $fields = json_decode(file_get_contents($file), true);
        } elseif ($input->getOption('delete')) {
            $table = $dataset->table($tableId);
            if (!$table->exists()) {
                throw new InvalidArgumentException('The supplied table does not exist');
            }
            if (!$input->isInteractive() && !$input->getOption('no-confirmation')) {
                throw new \LogicException(
                    '"no-confirmation" is required for deletion if the command is not interactive');
            }
            if (!$input->getOption('no-confirmation')) {
                $message = sprintf(
                    'Are you sure you want to delete the BigQuery table "%s"? [y/n]: ',
                    $tableId
                );
                if (!$question->ask($input, $output, new ConfirmationQuestion($message))) {
                    return $output->writeln('<error>Task cancelled by user.</error>');
                }
            }
            $table->delete();
            return $output->writeln('<info>Table deleted successfully</info>');
        } else {
            if (!$input->isInteractive()) {
                throw new \LogicException(
                    '"schema-json" is required if the command is not interactive');
            }
            $fields = $this->getFieldSchema($question, $input, $output);
        }
        $fieldsJson = json_encode($fields, JSON_PRETTY_PRINT);
        $message = $fieldsJson . "\nDoes this schema look correct? [y/n]: ";
        if ($input->isInteractive()) {
            if (!$question->ask($input, $output, new ConfirmationQuestion($message))) {
                return $output->writeln('<error>Task cancelled by user.</error>');
            }
        }
        try {
            $options = ['schema' => ['fields' => $fields]];
            $table = $dataset->createTable($tableId, $options);
        } catch (BadRequestException $e) {
            $response = $e->getServiceException()->getResponse();
            $errorJson = json_decode((string) $response->getBody(), true);
            $error = $errorJson['error']['errors'][0]['message'];
            $output->writeln(sprintf('<error>%s</error>', $error));
            throw $e;
        }

        $output->writeln('<info>Table created successfully</info>');
    }

    private function getFieldSchema($question, $input, $output, $prefix = '')
    {
        $schema = [];
        $fields = [
            'name' => null,
            'type' => [
                'string',
                'bytes',
                'integer',
                'float',
                'boolean',
                'timestamp',
                'date',
                'record',
            ],
            'mode' => [
                'nullable',
                'required',
                'repeated',
            ]
        ];
        for ($i = 0; true; $i++) {
            $schema[$i] = array();
            foreach ($fields as $field => $choices) {
                $message = sprintf('%s%s column %s',
                    $prefix,
                    $this->addNumberSuffix($i+1),
                    $field
                );
                if ($choices) {
                    $message .= sprintf(' (default: %s): ', $choices[0]);
                    $q = new ChoiceQuestion($message, $choices, 0);
                } else {
                    $q = new Question($message . ': ');
                }
                $q->setValidator($this->getNotEmptyValidator());
                $value = $question->ask($input, $output, $q);
                $schema[$i][$field] = $choices ? $choices[$value] : $value;
            }

            if ($schema[$i]['type'] === 'record') {
                $p = sprintf('%s[%s] ', $prefix, $schema[$i]['name']);
                $schema[$i]['fields'] = $this->getFieldSchema(
                    $question,
                    $input,
                    $output,
                    $p
                );
            }

            $q = new ConfirmationQuestion(sprintf(
                '%sadd another field? [y/n]: ',
                $prefix
            ));
            if (!$question->ask($input, $output, $q)) {
                break;
            }
        }

        return $schema;
    }

    private function getProjectIdFromGcloud()
    {
        exec("gcloud config list --format 'value(core.project)' 2>/dev/null", $output, $return_var);

        if (0 === $return_var) {
            return array_pop($output);
        }
    }

    private function getNotEmptyValidator()
    {
        return function ($value) {
            if (is_null($value)) {
                throw new \InvalidArgumentException('value required');
            }
            return $value;
        };
    }

    private function addNumberSuffix($i)
    {
        switch ($i % 10) {
            // Handle 1st, 2nd, 3rd
            case 1:  return $i . 'st';
            case 2:  return $i . 'nd';
            case 3:  return $i . 'rd';
        }

        return $i . 'th';
    }
}
