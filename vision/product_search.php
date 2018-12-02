<?php
/**
 * Copyright 2018 Google Inc.
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

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

$application = new Application('Product Search');

// import product set
$application->add((new Command('product-set-import'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('location', InputArgument::REQUIRED,
        'Name of compute region.')
    ->addArgument('gcs-uri', InputArgument::REQUIRED, 'GCS path to import file.')
    ->setDescription('Import images of different products in the product set.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> imports images of different products in the product set.
    <info>php %command.full_name% PROJECT_ID COMPUTE_REGION IMPORT_FILE_PATH</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $location = $input->getArgument('location');
        $gcsUri = $input->getArgument('gcs-uri');
        product_set_import($projectId, $location, $gcsUri);
    })
);

// create product set
$application->add((new Command('product-set-create'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('location', InputArgument::REQUIRED,
        'Name of compute region.')
    ->addArgument('product-set-id', InputArgument::REQUIRED, 'ID of product set')
    ->addArgument('product-set-display-name', InputArgument::REQUIRED, 'display name of product set')
    ->setDescription('Create a product set')
    ->setHelp(<<<EOF
The <info>%command.name%</info> creates a product set.
    <info>php %command.full_name% PROJECT_ID COMPUTE_REGION PRODUCT_SET_ID PRODUCT_SET_DISPLAY_NAME</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $location = $input->getArgument('location');
        $productSetId = $input->getArgument('product-set-id');
        $productSetDisplayName = $input->getArgument('product-set-display-name');
        product_set_create($projectId, $location, $productSetId, $productSetDisplayName);
    })
);

// create product
$application->add((new Command('product-create'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('location', InputArgument::REQUIRED,
        'Name of compute region.')
    ->addArgument('product-id', InputArgument::REQUIRED, 'ID of product')
    ->addArgument('product-display-name', InputArgument::REQUIRED, 'display name of product')
    ->addArgument('product-category', InputArgument::REQUIRED, 'Category of product')
    ->setDescription('Create a product.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> creates a product
    <info>php %command.full_name% PROJECT_ID COMPUTE_REGION PRODUCT_ID PRODUCT_DISPLAY_NAME PRODUCT_CATEGORY</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $location = $input->getArgument('location');
        $productId = $input->getArgument('product-id');
        $productDisplayName = $input->getArgument('product-display-name');
        $productCategory = $input->getArgument('product-category');
        product_create($projectId, $location, $productId, $productDisplayName, $productCategory);
    })
);

// list products
$application->add((new Command('product-list'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('location', InputArgument::REQUIRED,
        'Name of compute region.')
    ->setDescription('List products.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> lists all products
    <info>php %command.full_name% PROJECT_ID COMPUTE_REGION</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $location = $input->getArgument('location');
        product_list($projectId, $location);
    })
);

// get product
$application->add((new Command('product-get'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('location', InputArgument::REQUIRED,
        'Name of compute region.')
    ->addArgument('product-id', InputArgument::REQUIRED, 'ID of product')
    ->setDescription('Get information for a product.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> gets information for a product
    <info>php %command.full_name% PROJECT_ID COMPUTE_REGION PRODUCT_ID</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $location = $input->getArgument('location');
        $productId = $input->getArgument('product-id');
        product_get($projectId, $location, $productId);
    })
);

// update product labels
$application->add((new Command('product-update'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('location', InputArgument::REQUIRED,
        'Name of compute region.')
    ->addArgument('product-id', InputArgument::REQUIRED, 'ID of product')
    ->addArgument('key', InputArgument::REQUIRED, 'key of the label to update')
    ->addArgument('value', InputArgument::REQUIRED, 'value of the label to update')
    ->setDescription('Update product labels.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> updates product label
    <info>php %command.full_name% PROJECT_ID COMPUTE_REGION PRODUCT_ID LABEL_KEY LABEL_VALUE</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $location = $input->getArgument('location');
        $productId = $input->getArgument('product-id');
        $key = $input->getArgument('key');
        $value = $input->getArgument('value');
        product_update($projectId, $location, $productId, $key, $value);
    })
);

// delete product
$application->add((new Command('product-delete'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('location', InputArgument::REQUIRED,
        'Name of compute region.')
    ->addArgument('product-id', InputArgument::REQUIRED, 'ID of product')
    ->setDescription('Delete the product and all its reference images.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> deletes the product and all its reference images 
    <info>php %command.full_name% PROJECT_ID COMPUTE_REGION PRODUCT_ID</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $location = $input->getArgument('location');
        $productId = $input->getArgument('product-id');
        product_delete($projectId, $location, $productId);
    })
);

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
