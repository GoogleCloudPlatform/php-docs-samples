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
use Google\Cloud\Exception\BadRequestException;
use Exception;

/**
 * Command line utility to run a BigQuery query.
 *
 * Usage: php bigquery.php query
 */
class QueryCommand extends Command
{
    use ProjectIdTrait;

    protected function configure()
    {
        $this
            ->setName('query')
            ->setDescription('Run a BigQuery query')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command queries your dataset.

    <info>%command.full_name% "SELECT TOP(corpus, 3) as title, COUNT(*) as unique_words FROM [publicdata:samples.shakespeare]"</info>

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
            ->addOption(
                'standard-sql',
                null,
                InputOption::VALUE_NONE,
                'run the query using standard SQL instead of legacy SQL syntax'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $question = $this->getHelper('question');
        if (!$projectId = $input->getOption('project')) {
            $projectId = $this->getProjectIdFromGcloud();
        }
        $message = sprintf('<info>Running query for project %s</info>', $projectId);
        $output->writeln($message);
        if (!$query = $input->getArgument('query')) {
            if ($input->isInteractive()) {
                $q = new Question('Enter your query: ');
                $query = $question->ask($input, $output, $q);
            } else {
                throw new Exception('You must supply a query argument');
            }
        }

        try {
            if ($input->getOption('sync')) {
                run_query(
                    $projectId,
                    $query,
                    !$input->getOption('standard-sql'));
            } else {
                run_query_as_job(
                    $projectId,
                    $query,
                    !$input->getOption('standard-sql'));
            }
        } catch (BadRequestException $e) {
            $response = $e->getServiceException()->getResponse();
            $errorJson = json_decode((string) $response->getBody(), true);
            $error = $errorJson['error']['errors'][0]['message'];
            $output->writeln(sprintf('<error>%s</error>', $error));
            throw $e;
        }
    }
}
