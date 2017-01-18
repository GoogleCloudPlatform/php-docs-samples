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


namespace Google\Cloud\Samples\Vision;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to detect which language some text is written in.
 */
class DetectLogoCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('logo')
            ->setDescription('Detect logos in an image using '
                . 'Google Cloud Vision API')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command logos objects seen in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
            )
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The image to examine.'
            )
            ->addOption(
                'project',
                'p',
                InputOption::VALUE_REQUIRED,
                'Your Project ID.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            $result = require __DIR__ . '/snippets/detect_logo_gcs.php';
        } else {
            $result = require __DIR__ . '/snippets/detect_logo.php';
        }
    }
}
