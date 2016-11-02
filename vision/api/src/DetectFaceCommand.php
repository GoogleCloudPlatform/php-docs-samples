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

// [START face_detection]
use Google\Cloud\Vision\VisionClient;
// [END face_detection]
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to detect which language some text is written in.
 */
class DetectFaceCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('face')
            ->setDescription('Detect faces in an image using '
                . 'Google Cloud Vision API')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command finds faces in an image using
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
        $this->detectFace(
            $input->getOption('api-key'),
            $input->getArgument('path')
        );
    }

    // [START face_detection]
    /***
     * @param $apiKey string Your API key.
     * @param $path string The path to the image file.
     */
    protected function detectFace($apiKey, $path)
    {
        $vision = new VisionClient([
            'key' => $apiKey,
        ]);
        $image = $vision->image(file_get_contents($path), ['FACE_DETECTION']);
        $result = $vision->annotate($image);
        foreach ($result->info()['faceAnnotations'] as $annotation) {
            print("FACE\n");
            if (isset($annotation['boundingPoly'])) {
                print("  BOUNDING POLY\n");
                foreach ($annotation['boundingPoly']['vertices'] as $vertex) {
                    $x = isset($vertex['x']) ? $vertex['x'] : '';
                    $y = isset($vertex['y']) ? $vertex['y'] : '';
                    print("    x:$x\ty:$y\n");
                }
            }
            print("  LANDMARKS\n");
            foreach ($annotation['landmarks'] as $landmark) {
                $pos = $landmark['position'];
                print("    $landmark[type]:\tx:$pos[x]\ty:$pos[y]\tz:$pos[z]\n");
            }
            $scalar_features = [
                'rollAngle',
                'panAngle',
                'tiltAngle',
                'detectionConfidence',
                'landmarkingConfidence',
                'joyLikelihood',
                'sorrowLikelihood',
                'angerLikelihood',
                'surpriseLikelihood',
                'underExposedLikelihood',
                'blurredLikelihood',
                'headwearLikelihood'
            ];
            foreach ($scalar_features as $feature) {
                if (isset($annotation[$feature])) {
                    print("  $feature:\t$annotation[$feature]\n");
                }
            }
        }
    }
    // [END face_detection]
}
