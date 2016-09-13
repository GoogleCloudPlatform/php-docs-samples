<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Logging;

// [START list_sinks_use]
use Google\Cloud\Logging\LoggingClient;
// [END list_sinks_use]
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListSinksCommand
 * @package Google\Cloud\Samples\Logging
 *
 * This command simply list sinks
 */
class ListSinksCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('list-sinks')
            ->setDescription('Lists sinks');
        $this->addCommonOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectId = $input->getOption('project');
        //$loggerName = $input->getOption('logger');
        // [START list_sinks]
        $logging = new LoggingClient(['projectId' => $projectId]);
        foreach ($logging->sinks() as $sink) {
            /* @var $sink \Google\Cloud\Logging\Sink */
            foreach ($sink->info() as $key => $value) {
                print "$key:$value\n";
            }
            print PHP_EOL;
        }
        // [END list_sinks]
    }
}
