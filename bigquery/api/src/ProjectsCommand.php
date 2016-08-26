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
use Symfony\Component\Console\Output\OutputInterface;
use Google\Auth\CredentialsLoader;
use Exception;

/**
 * Command line utility to list BigQuery projects.
 *
 * Usage: php bigquery.php projects
 */
class ProjectsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('projects')
            ->setDescription('List BigQuery projects')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command lists all the projects associated with BigQuery.

    <info>php %command.full_name%</info>

EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$keyFile = CredentialsLoader::fromWellKnownFile()) {
            throw new Exception('Could not derive a key file. Run "gcloud auth login".');
        }
        list_projects();
    }

    private function getAccessTokenFromGcloud()
    {
        exec('gcloud beta auth application-default print-access-token 2>/dev/null', $output, $return_var);

        if (0 === $return_var) {
            return array_pop($output);
        }
    }
}
