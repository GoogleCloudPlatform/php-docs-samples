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
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Exception\BadRequestException;

/**
 * Command line utility to run a BigQuery query.
 */
class QueryCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('query')
            ->setDescription('Run a BigQuery query')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command queries your dataset
EOF
            )
            ->addArgument(
                'query',
                InputArgument::OPTIONAL,
                'The query to run'
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'The Google Cloud Platform project name to use for this invocation. ' .
                'If omitted then the current gcloud project is assumed. '
            )
            ->addOption(
                'sync',
                null,
                InputOption::VALUE_NONE,
                'run the query syncronously'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // set the output to globals for functions defined in functions.php
        $GLOBALS['output'] = $output;
        $question = $this->getHelper('question');
        if (!$projectId = $input->getOption('project')) {
            if (!$projectId = $this->getProjectIdFromGcloud()) {
                throw new \Exception('Could not derive a project ID from gloud. ' .
                    'You must supply a project ID using --project');
            }
        }
        $message = sprintf('<info>Running query for project %s</info>', $projectId);
        $output->writeln($message);
        if (!$query = $input->getArgument('query')) {
            if ($input->isInteractive()) {
                $q = new Question('Enter your query: ');
                $query = $question->ask($input, $output, $q);
            } else {
                throw new \Exception('You must supply a query argument');
            }
        }

        # [START build_service]
        $bigQuery = new BigQueryClient([
            'projectId' => $projectId
        ]);
        # [END build_service]
        $sync = $input->getOption('sync');
        try {
            $queryResults = $this->runQuery($bigQuery, $query, $sync);
        } catch (BadRequestException $e) {
            $response = $e->getServiceException()->getResponse();
            $errorJson = json_decode((string) $response->getBody(), true);
            $error = $errorJson['error']['errors'][0]['message'];
            $output->writeln(sprintf('<error>%s</error>', $error));
            throw $e;
        }

        # [START print_results]
        if ($queryResults->isComplete()) {
            $i = 0;
            $rows = $queryResults->rows();
            foreach ($rows as $row) {
                printf('--- Row %s ---' . PHP_EOL, ++$i);
                foreach ($row as $column => $value) {
                    printf('%s: %s' . PHP_EOL, $column, $value);
                }
            }
            printf('Found %s row(s)' . PHP_EOL, $i);
        } else {
            throw new \Exception('The query failed to complete');
        }
        # [END print_results]
    }

    public function runQuery(BigQueryClient $bigQuery, $query, $sync = false)
    {
        if ($sync) {
            # [START run_query]
            $queryResults = $bigQuery->runQuery($query);
            # [END run_query]
        } else {
            # [START run_query_async]
            $job = $bigQuery->runQueryAsJob($query);
            # [END run_query_async]
            # [START poll_job]
            $intervalMs = 2000; // check every 2 seconds
            while (true) {
                $job->reload();
                if ($job->isComplete()) {
                    break;
                }
                usleep(1000 * $intervalMs);
            }
            $queryResults = $job->queryResults();
            # [END poll_job]
        }
        return $queryResults;
    }

    private function getProjectIdFromGcloud()
    {
        exec("gcloud config list --format 'value(core.project)' 2>/dev/null", $output, $return_var);

        if (0 === $return_var) {
            return array_pop($output);
        }
    }
}
