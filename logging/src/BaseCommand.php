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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A base class for commands which needs project id.
 */
abstract class BaseCommand extends Command
{
    /**
     * Add --project and --logger options.
     */
    protected function addCommonOptions()
    {
        $this->addOption(
            'project',
            null,
            InputOption::VALUE_REQUIRED,
            'The Google Cloud Platform project name to use for this command. ' .
            'If omitted then the current gcloud project is assumed. ',
            $this->getProjectIdFromGcloud()
        );
        $this->addOption(
            'logger',
            null,
            InputOption::VALUE_OPTIONAL,
            'The name of the logger. By naming a logger, you can logically '
            . 'treat log entries in a logger; e.g. you can list or delete '
            . 'all the log entries by the name of the logger.',
            'my_logger'
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (empty($input->getOption("project"))) {
            throw new \Exception("Project ID not specified");
        }
    }

    /**
     * Detect the current project id configured by gcloud sdk.
     *
     * @return string|null detected projectId or null upon failure
     */
    private function getProjectIdFromGcloud()
    {
        exec(
            "gcloud config list --format 'value(core.project)' 2>/dev/null",
            $output,
            $return_var
        );
        if (0 === $return_var) {
            return array_pop($output);
        }
        return null;
    }
}
