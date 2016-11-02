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

// [START image_property_detection]
use Google\Cloud\Vision\VisionClient;
// [END image_property_detection]
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to detect which language some text is written in.
 */
class DetectImagePropertyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('property')
            ->setDescription('Detect image proerties in an image using '
                . 'Google Cloud Vision API')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command detects image properties in an image
using the Google Cloud Vision API.

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
        $this->detectImageProperty(
            $input->getOption('api-key'),
            $input->getArgument('path')
        );
    }

    // [START image_property_detection]
    /***
     * @param $apiKey string Your API key.
     * @param $path string The path to the image file.
     */
    protected function detectImageProperty($apiKey, $path)
    {
        $vision = new VisionClient([
            'key' => $apiKey,
        ]);
        $image = $vision->image(file_get_contents($path),
            ['IMAGE_PROPERTIES']);
        $result = $vision->annotate($image);
        var_dump($result);
        $annotation = $result->info()['imagePropertiesAnnotation'];
        print("COLORS\n");
        foreach ($annotation['dominantColors']['colors'] as $color) {
            $rgb = $color['color'];
            print("  COLOR\n");
            print("  red:$rgb[red]\tgreen:$rgb[green]\tblue:$rgb[blue]\n");
            print("  score:$color[score]\n");
            print("  pixelFraction:$color[pixelFraction]\n");
        }
    }
    // [END image_property_detection]
}
