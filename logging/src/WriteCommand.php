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

// [START write_log_use]
use Google\Cloud\Logging\LoggingClient;
// [END write_log_use]
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WriteCommand
 * @package Google\Cloud\Samples\Logging
 *
 * This command simply writes a log message via Logging API.
 */
class WriteCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('write')
            ->setDescription('Writes log entries to the given logger')
            ->addArgument(
                "message",
                InputArgument::OPTIONAL,
                "The log message to write",
                "Hello"
            );
        $this->addCommonOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getArgument('message');
        $projectId = $input->getOption('project');
        $loggerName = $input->getOption('logger');
        // [START write_log]
        $logging = new LoggingClient(['projectId' => $projectId]);
        $logger = $logging->logger($loggerName);
        $entry = $logger->entry($message, [
            'type' => 'gcs_bucket',
            'labels' => [
                'bucket_name' => 'my_bucket'
            ]
        ]);
        $logger->write($entry);
        // [END write_log]
        printf("Wrote a log to a logger '%s'." . PHP_EOL, $loggerName);
    }
}
