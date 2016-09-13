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

// [START delete_logger_use]
use Google\Cloud\Logging\LoggingClient;
// [END delete_logger_use]
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteLoggerCommand
 * @package Google\Cloud\Samples\Logging
 *
 * This command deletes a logger and all its entries.
 */
class DeleteLoggerCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('delete-logger')
            ->setDescription('Deletes the given logger and its entries');
        $this->addCommonOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectId = $input->getOption('project');
        $loggerName = $input->getOption('logger');
        // [START delete_logger]
        $logging = new LoggingClient(['projectId' => $projectId]);
        $logger = $logging->logger($loggerName);
        $logger->delete();
        // [END delete_logger]
        printf("Deleted a logger '%s'." . PHP_EOL, $loggerName);
    }
}
