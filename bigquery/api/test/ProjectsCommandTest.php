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

namespace Google\Cloud\Samples\BigQuery\Tests;

use Google\Auth\CredentialsLoader;
use Google\Cloud\Samples\BigQuery\ProjectsCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for ProjectsCommand.
 */
class ProjectsCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testProjects()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        if (!CredentialsLoader::fromWellKnownFile()) {
            if (!$keyFile = getenv('GOOGLE_KEY_FILE')) {
                $this->markTestSkipped('No key file');
            }
            if (!$home = getenv('HOME')) {
                $this->markTestSkipped('No home directory for key file');
            }
            $path = sprintf('%s/.config/gcloud/', $home);
            @mkdir($path, 0777, true);
            file_put_contents(
                $path . '/application_default_credentials.json',
                $keyFile
            );
        }
        $application = new Application();
        $application->add(new ProjectsCommand());
        $commandTester = new CommandTester($application->get('projects'));
        $commandTester->execute(
            [],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/$projectId/");
    }
}
