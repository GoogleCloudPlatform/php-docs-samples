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
use Symfony\Component\Console\Input\InputDefinition;

# includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Vision');

$inputDefinition = new InputDefinition([
    new InputArgument('path', InputArgument::REQUIRED, 'The image to examine.'),
    new InputArgument('output', InputArgument::OPTIONAL, 'The output file'),
]);

// detect label command
$application->add((new Command('label'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect labels in an image using Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command labels objects seen in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_label_gcs($path);
        } else {
            detect_label($path);
        }
    })
);

// detect text command
$application->add((new Command('text'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect text in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command prints text seen in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_text_gcs($path);
        } else {
            detect_text($path);
        }
    })
);

// detect face command
$application->add((new Command('face'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect faces in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command finds faces in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        $outFile = $input->getArgument('output');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_face_gcs($path, $outFile);
        } else {
            detect_face($path, $outFile);
        }
    })
);

// detect landmark command
$application->add((new Command('landmark'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect landmarks in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command finds landmarks in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_landmark_gcs($path);
        } else {
            detect_landmark($path);
        }
    })
);

// detect logo command
$application->add((new Command('logo'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect logos in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command finds logos in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_logo_gcs($path);
        } else {
            detect_logo($path);
        }
    })
);

// detect safe search command
$application->add((new Command('safe-search'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect adult content in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects adult content in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_safe_search_gcs($path);
        } else {
            detect_safe_search($path);
        }
    })
);

// detect image property command
$application->add((new Command('property'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect image proerties in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects image properties in an image
using the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_image_property_gcs($path);
        } else {
            detect_image_property($path);
        }
    })
);

// detect crop hints command
$application->add((new Command('crop-hints'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect crop hints in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command prints crop hints for an image using
the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_crop_hints_gcs($path);
        } else {
            detect_crop_hints($path);
        }
    })
);

// detect document text command
$application->add((new Command('document-text'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect document text in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command prints document text for an image using
the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_document_text_gcs($path);
        } else {
            detect_document_text($path);
        }
    })
);

// detect pdf command
$application->add((new Command('pdf'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect text from PDF/TIFF using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command prints document text for a PDF/TIFF using
the Google Cloud Vision API.

    <info>php %command.full_name% gs://path/to/document.pdf gs://</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        $output = $input->getArgument('output');
        detect_pdf_gcs($path,$output);
    })
);

// detect web command
$application->add((new Command('web'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect web references to an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command prints web references to an image using
the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_web_gcs($path);
        } else {
            detect_web($path);
        }
    })
);

// detect web with geo command
$application->add((new Command('web-geo'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect web entities to an image with geo metadata '
                . 'using Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command prints web entities to an image with 
geo metadata using the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_web_with_geo_metadata_gcs($path);
        } else {
            detect_web_with_geo_metadata($path);
        }
    })
);

// localize object command
$application->add((new Command('localize-object'))
    ->setDefinition($inputDefinition)
    ->setDescription('Localize object in an image using '
                . 'Google Cloud Vision API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command finds objects in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% path/to/image.png</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $path = $input->getArgument('path');
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $path)) {
            detect_object_gcs($path);
        } else {
            detect_object($path);
        }
    })
);

if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
