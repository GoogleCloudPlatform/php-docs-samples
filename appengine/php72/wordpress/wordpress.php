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

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Google\Cloud\Utils\WordPressProject;

$application = new Application('Cloud Video Intelligence');

$wordPressOptions = new InputDefinition([
    new InputOption('project_id', null, InputOption::VALUE_REQUIRED, 'Google Cloud project id'),
    new InputOption('db_region', null, InputOption::VALUE_REQUIRED, 'Cloud SQL region'),
    new InputOption('db_instance', null, InputOption::VALUE_REQUIRED, 'Cloud SQL instance id'),
    new InputOption('db_name', null, InputOption::VALUE_REQUIRED, 'Cloud SQL database name'),
    new InputOption('db_user', null, InputOption::VALUE_REQUIRED, 'Cloud SQL database username'),
    new InputOption('db_password', null, InputOption::VALUE_REQUIRED, 'Cloud SQL database password'),
    new InputOption('local_db_user', null, InputOption::VALUE_REQUIRED, 'Local SQL database username'),
    new InputOption('local_db_password', null, InputOption::VALUE_REQUIRED, 'Local SQL database password'),
]);

$application = new Application('Wordpress Helper');
$application->add(new Command('create'))
    ->setDescription('Create a new WordPress site for Google Cloud')
    ->setDefinition(clone $wordPressOptions)
    ->addOption('dir', null, InputOption::VALUE_REQUIRED, 'Directory for the new project', WordPressProject::DEFAULT_DIR)
    ->addOption('wordpress_url', null, InputOption::VALUE_REQUIRED, 'URL of the WordPress archive', WordPressProject::LATEST_WP)
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $wordpress = new WordPressProject($input, $output);
        // Run the wizard to prompt user for project and database parameters.
        $dir = $wordpress->promptForProjectDir();

        // download wordpress
        $wordpress->downloadWordpress($dir);

        // initialize the project and download the plugins
        $wordpress->initializeProject($dir);
        $wordpress->downloadGcsPlugin();

        $dbParams = $wordpress->initializeDatabase();

        // populate random key params
        $params = $dbParams + $wordpress->generateRandomValueParams();

        // copy all the sample files into the project dir and replace parameters
        $wordpress->copyFiles(__DIR__ . '/files', [
            '.gcloudignore' => '/',
            'app.yaml' => '/',
            'cron.yaml' => '/',
            'php.ini' => '/',
            'gae-app.php' => '/',
            'wp-config.php' => '/',
        ], $params);

        $output->writeln("<info>Your WordPress project is ready at $dir</info>");
    });

$application->add(new Command('update'))
    ->setDescription('Update an existing WordPress site for Google Clud')
    ->setDefinition(clone $wordPressOptions)
    ->addArgument('dir', InputArgument::REQUIRED, 'Directory for the existing project')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        // use the supplied dir for the wordpress project
        $dir = $input->getArgument('dir');

        $wordpress = new WordPressProject($input, $output);
        $wordpress->initializeProject($dir);

        // Download the plugins if they don't exist
        if (!file_exists($dir . '/wp-content/plugins/gcs')) {
            $wordpress->downloadGcsPlugin();
        }

        $dbParams = $wordpress->initializeDatabase();

        // populate random key params
        $params = $dbParams + $wordpress->generateRandomValueParams();

        // copy all the sample files into the project dir and replace parameters
        $wordpress->copyFiles(__DIR__ . '/files', [
            '.gcloudignore' => '/',
            'app.yaml' => '/',
            'cron.yaml' => '/',
            'php.ini' => '/',
            'gae-app.php' => '/',
            'wp-config.php' => '/',
        ], $params);

        $output->writeln("<info>Your WordPress project has been updated at $dir</info>");
    });

if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
