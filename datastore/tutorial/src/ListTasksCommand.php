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

use Google\Cloud\Datastore\Entity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListTasksCommand
 * @package Google\Cloud\Samples\Datastore\Tasks
 *
 * List all the tasks in ascending order of creation time.
 */
class ListTasksCommand extends Command
{
    protected function configure()
    {
        $this->setName('list-tasks')
            ->setDescription(
                'List all the tasks in ascending order of creation time')
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
        $result = list_tasks($datastore);
        $table = new Table($output);
        $table->setHeaders(array('ID', 'Description', 'Status', 'Created'));
        /* @var Entity $task */
        foreach ($result as $index => $task) {
            $done = $task['done'] ? 'done' : 'created';
            $table->setRow(
                $index,
                array(
                    $task->key()->pathEnd()['id'],
                    $task['description'],
                    $done,
                    $task['created']->format('Y-m-d H:i:s e')
                )
            );
        }
        $table->render();
    }
}
