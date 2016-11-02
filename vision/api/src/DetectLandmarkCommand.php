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

// [START landmark_detection]
use Google\Cloud\Vision\VisionClient;
// [END landmark_detection]
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to detect which language some text is written in.
 */
class DetectLandmarkCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('landmark')
            ->setDescription('Detect landmarks in an image using '
                . 'Google Cloud Vision API')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command landmarks objects seen in an image using
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
        $this->detectLandmark(
            $input->getOption('api-key'),
            $input->getArgument('path')
        );
    }

    // [START landmark_detection]
    /***
     * @param $apiKey string Your API key.
     * @param $path string The path to the image file.
     */
    protected function detectLandmark($apiKey, $path)
    {
        $vision = new VisionClient([
            'key' => $apiKey,
        ]);
        $image = $vision->image(file_get_contents($path), ['LANDMARK_DETECTION']);
        $result = $vision->annotate($image);
        foreach ($result->info()['landmarkAnnotations'] as $annotation) {
            print("LANDMARK\n");
            print("  mid: $annotation[mid]\n");
            print("  description: $annotation[description]\n");
            print("  score: $annotation[score]\n");
            if (isset($annotation['boundingPoly'])) {
                print("  BOUNDING POLY\n");
                foreach ($annotation['boundingPoly']['vertices'] as $vertex) {
                    print("    x:$vertex[x]\ty:$vertex[y]\n");
                }
            }
            foreach ($annotation['locations'] as $location) {
                if (isset($location['latLng'])) {
                    $ll = $location['latLng'];
                    print("  LOCATION:\tlatitude:$ll[latitude]\t" .
                        "longitude:$ll[longitude]\n");
                }
            }
        }
    }
    // [END landmark_detection]
}
