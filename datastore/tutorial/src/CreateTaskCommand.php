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

namespace Google\Cloud\Samples\Datastore\Tasks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateTaskCommand
 * @package Google\Cloud\Samples\Datastore\Tasks
 *
 * Create a new task with a given description.
 */
class CreateTaskCommand extends Command
{
    protected function configure()
    {
        $this->setName('new')
            ->setDescription('Adds a task with a description')
            ->addArgument(
                'description',
                InputArgument::REQUIRED,
                'The description of the new task'
            )
            ->addOption(
                'project-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Your cloud project id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectId = $input->getOption('project-id');
        if (!empty($projectId)) {
            $datastore = build_datastore_service($projectId);
        } else {
            $datastore = build_datastore_service_with_namespace();
        }
        $description = $input->getArgument('description');
        $task = add_task($datastore, $description);
        $output->writeln(
            sprintf(
                'Created new task with ID %d.', $task->key()->pathEnd()['id']
            )
        );
    }
}
