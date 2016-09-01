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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Trait for determining the current project ID using "gcloud"
 */
trait GoogleProjectTrait
{
    /**
     * Add project option to $this.
     */
    private function addProjectOption()
    {
        $this->addOption(
            'project',
            null,
            InputOption::VALUE_REQUIRED,
            'The Google Cloud Platform project name to use for this command. ' .
            'If omitted then the current gcloud project is assumed. ',
            $this->getProjectIdFromGcloud()
        );
    }

    /**
     * @inheritdoc
     */
    protected function interact(InputInterface $input)
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