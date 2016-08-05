<?php

namespace Google\Cloud\Samples\BigQuery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\ExponentialBackoff;
use Google\Cloud\Storage\StorageClient;

/**
*
*/
class ImportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('import')
            ->setDescription('BigQuery import command')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command imports your data into BigQuery from
a file, Datastore, or Cloud Storage.

Import a JSON file

    <info>php %command.full_name% DATASET_ID TABLE_NAME /path/to/my_data.json</info>

Import from Google Cloud Storage

    <info>php %command.full_name% DATASET_ID TABLE_NAME gs://my_bucket/my_data.csv</info>

Import from Google Datastore

    <info>php %command.full_name% DATASET_ID TABLE_NAME gs://my_bucket/datastore_entity.backup_info</info>

EOF
            )
            ->addArgument(
                'dataset',
                InputArgument::REQUIRED,
                'The dataset to import to'
            )
            ->addArgument(
                'table',
                InputArgument::REQUIRED,
                'The table to import to'
            )
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
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
        if (!$projectId = $input->getOption('project')) {
            if ($projectId = $this->getProjectIdFromGcloud()) {
                if ($input->isInteractive()) {
                    $question = $this->getHelper('question');
                    $message = sprintf('Import data for project %s? [y/n]: ', $projectId);
                    if (!$question->ask($input, $output, new ConfirmationQuestion($message))) {
                        return $output->writeln('<error>Task aborted by user</error>');
                    }
                }
            } else {
                throw new \Exception('Could not derive a project ID from gloud. ' .
                    'You must supply a project ID using --project');
            }
        }
        $datasetId = $input->getArgument('dataset');
        $tableId = $input->getArgument('table');

        # [START build_service]
        $bigQuery = new BigQueryClient([
            'projectId' => $projectId
        ]);
        $dataset = $bigQuery->dataset($datasetId);
        $table = $dataset->table($tableId);
        # [END build_service]

        if (!$dataset->exists()) {
            throw new \Exception('The supplied dataset does not exist for this project');
        }
        if (!$table->exists()) {
            throw new \Exception('The supplied table does not exist for this project. ' .
                'Create a schema in the UI or use the "schema" command');
        }

        $source = $input->getArgument('source');
        if (0 === strpos($source, 'gs://')) {
            $storage = new StorageClient([
                'projectId' => $projectId
            ]);
            $source = substr($source, 5);
            if (false === strpos($source, ':')) {
                throw new \Exception('Source does not contain object name');
            }
            list($bucketName, $objectName) = explode(':', $source, 2);
            $options = [];
            if ('.backup_info' === substr($source, -10)) {
                $options['sourceFormat'] = 'DATASTORE_BACKUP';
            }

            $object = $storage->bucket($bucketName)->object($objectName);
            $table->loadFromStorage($object, $options);
        } else {
            if (!(file_exists($source) && is_readable($source))) {
                throw new \Exception('Source file does not exist or is not readable');
            }

            $options = [];
            $pathInfo = pathinfo($source);
            if ('csv' === $pathInfo['extension']) {
                $options['sourceFormat'] = 'CSV';
            } elseif ('json' === $pathInfo['extension']) {
                $options['sourceFormat'] = 'NEWLINE_DELIMITED_JSON';
            } else {
                throw new \Exception('Source format unknown. Must be JSON or CSV');
            }

            # [START import]
            $job = $table->load(fopen($source, 'r'), $options);
            $backoff = new ExponentialBackoff(10);
            $backoff->execute(function() use ($job, $output) {
                $output->writeln('Waiting for job to complete');
                $job->reload();
                if (!$job->isComplete()) {
                    throw new \Exception('Job is not completed yet', 500);
                }
            });
            # [END import]

            $output->writeln('<info>Data imported successfully</info>');
        }
    }

    private function getProjectIdFromGcloud()
    {
        exec("gcloud config list --format 'value(core.project)' 2>/dev/null", $output, $return_var);

        if (0 === $return_var) {
            return array_pop($output);
        }
    }
}