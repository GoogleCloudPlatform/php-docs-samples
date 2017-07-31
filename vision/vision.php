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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Vision');

$inputDefinition = new InputDefinition([
    new InputArgument('path', InputArgument::REQUIRED, 'The image to examine.'),
    new InputOption('project', 'p', InputOption::VALUE_REQUIRED, 'The project id'),
    new InputArgument('output', InputArgument::OPTIONAL, 'The output file'),
]);

// Create Detect Label command
$application->add((new Command('label'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect labels in an image using Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command labels objects seen in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            detect_label_gcs($projectId, $bucketName, $objectName);
        } else {
            detect_label($projectId, $path);
        }
    })
);

// Create Detect Text command
$application->add((new Command('text'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect text in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command prints text seen in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            detect_text_gcs($projectId, $bucketName, $objectName);
        } else {
            detect_text($projectId, $path);
        }
    })
);

// Create Detect Face command
$application->add((new Command('face'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect faces in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command finds faces in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            $result = detect_face_gcs($projectId, $bucketName, $objectName);
        } else {
            $result = detect_face($projectId, $path);
        }
        $imageCreateFunc = [
            'png' => 'imagecreatefrompng',
            'gd' => 'imagecreatefromgd',
            'gif' => 'imagecreatefromgif',
            'jpg' => 'imagecreatefromjpeg',
            'jpeg' => 'imagecreatefromjpeg',
        ];
        $imageWriteFunc = [
            'png' => 'imagepng',
            'gd' => 'imagegd',
            'gif' => 'imagegif',
            'jpg' => 'imagejpeg',
            'jpeg' => 'imagejpeg',
        ];
        if (
            isset($result->info()['faceAnnotations'])
            && $outFile = $input->getArgument('output')
        ) {
            copy($path, $outFile);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, array_keys($imageCreateFunc))) {
                throw new \Exception('Unsupported image extension');
            }
            $outputImage = call_user_func($imageCreateFunc[$ext], $outFile);
            # [START highlight_image]
            foreach ($result->info()['faceAnnotations'] as $annotation) {
                if (isset($annotation['boundingPoly'])) {
                    $verticies = $annotation['boundingPoly']['vertices'];
                    $x1 = isset($verticies[0]['x']) ? $verticies[0]['x'] : 0;
                    $y1 = isset($verticies[0]['y']) ? $verticies[0]['y'] : 0;
                    $x2 = isset($verticies[2]['x']) ? $verticies[2]['x'] : 0;
                    $y2 = isset($verticies[2]['y']) ? $verticies[2]['y'] : 0;
                    imagerectangle($outputImage, $x1, $y1, $x2, $y2, 0x00ff00);
                }
            }
            # [END highlight_image]
            call_user_func($imageWriteFunc[$ext], $outputImage, $outFile);
            printf('Output image written to %s' . PHP_EOL, $outFile);
        }
    })
);

// Create Detect Landmark command
$application->add((new Command('landmark'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect landmarks in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command finds landmarks in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            detect_landmark_gcs($projectId, $bucketName, $objectName);
        } else {
            detect_landmark($projectId, $path);
        }
    })
);

// Create Detect Logo command
$application->add((new Command('logo'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect logos in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command finds logos in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            detect_logo_gcs($projectId, $bucketName, $objectName);
        } else {
            detect_logo($projectId, $path);
        }
    })
);

// Detect Safe Search command
$application->add((new Command('safe-search'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect adult content in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects adult content in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            detect_safe_search_gcs($projectId, $bucketName, $objectName);
        } else {
            detect_safe_search($projectId, $path);
        }
    })
);

// Detect Image Property command
$application->add((new Command('property'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect image proerties in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects image properties in an image
using the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            detect_image_property_gcs($projectId, $bucketName, $objectName);
        } else {
            detect_image_property($projectId, $path);
        }
    })
);

// Detect Crop Hints command
$application->add((new Command('crop-hints'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect crop hints in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command prints crop hints for an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            detect_crop_hints_gcs($projectId, $bucketName, $objectName);
        } else {
            detect_crop_hints($projectId, $path);
        }
    })
);

// Detect Document Text command
$application->add((new Command('document-text'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect document text in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command prints document text for an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            detect_document_text_gcs($projectId, $bucketName, $objectName);
        } else {
            detect_document_text($projectId, $path);
        }
    })
);

// Detect Web command
$application->add((new Command('web'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect web references to an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command prints web references to an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
            detect_web_gcs($projectId, $bucketName, $objectName);
        } else {
            detect_web($projectId, $path);
        }
    })
);

if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
