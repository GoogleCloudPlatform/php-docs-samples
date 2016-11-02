<?php

/**
 * Copyright 2016 Google Inc. All Rights Reserved.
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

namespace Google\Cloud\Samples\Vision;

// [START safe_search_detection]
use Google\Cloud\Vision\VisionClient;
// [END safe_search_detection]
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to detect which language some text is written in.
 */
class DetectSafeSearchCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('safe-search')
            ->setDescription('Detect adult content in an image using '
                . 'Google Cloud Vision API')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command detects adult content in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
            )
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The text to examine.'
            )
            ->addOption(
                'api-key',
                'k',
                InputOption::VALUE_REQUIRED,
                'Your API key.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectSafeSearch(
            $input->getOption('api-key'),
            $input->getArgument('path')
        );
    }

    // [START safe_search_detection]
    /***
     * @param $apiKey string Your API key.
     * @param $path string The path to the image file.
     */
    protected function detectSafeSearch($apiKey, $path)
    {
        $vision = new VisionClient([
            'key' => $apiKey,
        ]);
        $image = $vision->image(file_get_contents($path),
            ['SAFE_SEARCH_DETECTION']);
        $result = $vision->annotate($image);
        $annotation = $result->info()['safeSearchAnnotation'];
        print("SAFE_SEARCH\n");
        $scalar_features = ['adult', 'spoof', 'medical', 'violence'];
        foreach ($scalar_features as $feature) {
            if (isset($annotation[$feature])) {
                print("  $feature:\t$annotation[$feature]\n");
            }
        }
    }
    // [END safe_search_detection]
}
