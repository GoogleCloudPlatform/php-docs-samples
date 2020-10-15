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

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Google\Cloud\Utils\WordPress\Project;

$application = new Application('Wordpress Helper');
$application->add(new Command('setup'))
    ->setDescription('Setup WordPress on GCP')
    ->addOption('dir', null, InputOption::VALUE_REQUIRED, 'Directory for the new project', Project::DEFAULT_DIR)
    ->addOption('project_id', null, InputOption::VALUE_REQUIRED, 'Google Cloud project id')
    ->addOption('db_region', null, InputOption::VALUE_REQUIRED, 'Cloud SQL region')
    ->addOption('db_instance', null, InputOption::VALUE_REQUIRED, 'Cloud SQL instance id', 'wp')
    ->addOption('db_name', null, InputOption::VALUE_REQUIRED, 'Cloud SQL database name', 'wp')
    ->addOption('db_user', null, InputOption::VALUE_REQUIRED, 'Cloud SQL database username', 'wp')
    ->addOption('db_password', null, InputOption::VALUE_REQUIRED, 'Cloud SQL database password')
    ->addOption('local_db_user', null, InputOption::VALUE_REQUIRED, 'Local SQL database username')
    ->addOption('local_db_password', null, InputOption::VALUE_REQUIRED, 'Local SQL database password')
    ->addOption('wordpress_url', null, InputOption::VALUE_REQUIRED, 'URL of the WordPress archive', Project::LATEST_WP)
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $wordpress = new Project($input, $output);

        // Run the wizard to prompt user for project and database parameters.
        $dir = $wordpress->initializeProject();
        $dbParams = $wordpress->initializeDatabase();

        // download wordpress and plugins
        $wordpress->downloadWordpress();
        $wordpress->downloadBatcachePlugin();
        $wordpress->downloadMemcachedPlugin();
        $wordpress->downloadAppEnginePlugin();

        // populate random key params
        $params = $dbParams + $wordpress->generateRandomValueParams();

        // copy all the sample files into the project dir and replace parameters
        $wordpress->copyFiles(__DIR__ . '/files', [
            'app.yaml' => '/',
            'composer.json' => '/',
            'cron.yaml' => '/',
            'php.ini' => '/',
            'wp-cli.yml' => '/',
            'wp-config.php' => '/wordpress/',
        ], $params);

        // run composer in the project directory
        $wordpress->runComposer();

        $output->writeln("<info>Your WordPress project is ready at $dir</info>");
    });

if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
