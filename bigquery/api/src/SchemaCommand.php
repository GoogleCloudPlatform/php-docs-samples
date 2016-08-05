<?php

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
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Exception\BadRequestException;

/**
*
*/
class SchemaCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('schema')
            ->setDescription('BigQuery schema command')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command is an interactive tool for creating a BigQuery table
and defining a schema.

    <info>php %command.full_name% DATASET_ID</info>

EOF
            )
            ->addArgument(
                'dataset',
                InputArgument::REQUIRED,
                'The dataset to import to'
            )
            ->addArgument(
                'table',
                InputArgument::OPTIONAL,
                'The table to import to'
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
        $message = sprintf('<info>Creating schema for project %s</info>', $projectId);
        $output->writeln($message);

        $datasetId = $input->getArgument('dataset');
        # [START build_service]
        $bigQuery = new BigQueryClient([
            'projectId' => $projectId
        ]);
        # [END build_service]

        $dataset = $bigQuery->dataset($datasetId);
        if (!$dataset->exists()) {
            $message = sprintf('Dataset %s does not exist. Create it? [y/n]: ', $datasetId);
            $q = new ConfirmationQuestion($message);
            if (!$question->ask($input, $output, $q)) {
                return $output->writeln('<error>Task aborted by user</error>');
            }
            $dataset = $bigQuery->createDataset($datasetId);
        }

        if (!$tableId = $input->getArgument('table')) {
            $q = new Question('Enter a BigQuery table name: ');
            $q->setValidator($this->getNotEmptyValidator());
            $tableId = $question->ask($input, $output, $q);
        }
        if ($file = $input->getArgument('schema-json')) {
            $fields = json_decode(file_get_contents($file), true);
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
                return $output->writeln('<error>Task aborted by user</error>');
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