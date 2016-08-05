<?php

namespace Google\Cloud\Samples\BigQuery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Storage\StorageClient;

/**
*
*/
class QueryCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('query')
            ->setDescription('BigQuery query command')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command queries your dataset
EOF
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'The Google Cloud Platform project name to use for this invocation. ' .
                'If omitted then the current gcloud project is assumed. '
            )
            ->addOption(
                'query',
                null,
                InputOption::VALUE_REQUIRED,
                'The query to run'
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
        $message = sprintf('<info>Running query for project %s</info>', $projectId);
        $output->writeln($message);
        if (!$query = $input->getOption('query')) {
            $q = new Question('Enter your query: ');
            $query = $question->ask($input, $output, $q);
        }

        # [START build_service]
        $bigQuery = new BigQueryClient([
            'projectId' => $projectId
        ]);
        # [END build_service]
        # [START run_query]
        $queryResults = $bigQuery->runQuery($query);
        # [END run_query]

        # [START print_results]
        if ($queryResults->isComplete()) {
            $rows = $queryResults->rows();
            foreach ($rows as $i => $row) {
                printf("--- %s ---\n", $i);
                foreach ($row as $column => $value) {
                    printf("%s: %s\n", $column, $value);
                }
                printf("\n");
            }
        }
        # [END print_results]
    }

    private function getProjectIdFromGcloud()
    {
        exec("gcloud config list --format 'value(core.project)' 2>/dev/null", $output, $return_var);

        if (0 === $return_var) {
            return array_pop($output);
        }
    }
}