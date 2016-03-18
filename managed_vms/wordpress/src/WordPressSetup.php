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

namespace Google\Cloud\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class WordPressSetup extends Command
{
    const DEFAULT_DIR = 'my-wordpress-project';
    const DEFAULT_ERROR = 1;
    const LATEST_WP = 'https://wordpress.org/latest.tar.gz';
    const LATEST_BATCACHE =
        'https://downloads.wordpress.org/plugin/batcache.1.4.zip';
    const LATEST_MEMCACHED =
        'https://downloads.wordpress.org/plugin/memcached.2.0.3.zip';

    const FLEXIBLE_ENV = 'Flexible Environment';
    const STANDARD_ENV = 'Standard Environment';

    protected function configure()
    {
        $this
            ->setName('setup')
            ->setDescription('Setup WordPress on GCP')
            ->addOption(
                'env',
                'e',
                InputOption::VALUE_OPTIONAL,
                'App Engine environment to use; f: '
                . self::FLEXIBLE_ENV
                . ', s: '
                . self::STANDARD_ENV
                . '.',
                null
            )
            ->addOption(
                'dir',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Directory for the new project',
                self::DEFAULT_DIR
            )
            ->addOption(
                'project_id',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Google Cloud project id',
                ''
            )
            ->addOption(
                'db_instance',
                null,
                InputOption::VALUE_OPTIONAL,
                'Cloud SQL instance id',
                ''
            )
            ->addOption(
                'db_name',
                null,
                InputOption::VALUE_OPTIONAL,
                'Cloud SQL database name',
                ''
            )
            ->addOption(
                'db_user',
                null,
                InputOption::VALUE_OPTIONAL,
                'Cloud SQL database username',
                ''
            )
            ->addOption(
                'db_password',
                null,
                InputOption::VALUE_OPTIONAL,
                'Cloud SQL database password',
                ''
            );
    }

    protected function report(OutputInterface $output, ReportInterface $report)
    {
        foreach ($report->getInfo() as $value) {
            $output->writeln("<info>" . $value . "</info>");
        }
        if ($report->getErrors() === false) {
            return true;
        }
        foreach ($report->getErrors() as $value) {
            $output->writeln("<error>" . $value . "</error>");
        }
        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $configKeys = array(
            'project_id' => '',
            'db_instance' => 'wp',
            'db_name' => 'wp',
            'db_user' => 'wp',
            'db_password' => ''
        );
        $copyFiles = array(
            'app.yaml' => '/',
            'composer.json' => '/',
            'deploy_wrapper.sh' => '/',
            'Dockerfile' => '/',
            'gcs-media.php' => '/wordpress/wp-content/plugins/',
            'nginx-app.conf' => '/',
            'php.ini' => '/',
            'wp-config.php' => '/wordpress/',
            'wp.php' => '/'
        );
        $authKeys = array(
            'auth_key', 'secure_auth_key', 'logged_in_key', 'nonce_key',
            'auth_salt', 'secure_auth_salt', 'logged_in_salt', 'nonce_salt'
        );
        $dir = $input->getOption('dir');
        if ($dir === self::DEFAULT_DIR) {
            $q = new Question(
                'Please enter a directory path for the new project '
                . '(defaults to ' . $dir . '):',
                $dir
            );
            $dir = $helper->ask($input, $output, $q);
        }
        $q = new ConfirmationQuestion(
            'We will use the directory: <info>' . $dir . '</info>'
            . '. If the directory exists, we will override the contents. '
            . 'Do you want to continue? (Y/n)',
            true
        );
        if (!$helper->ask($input, $output, $q)) {
            $output->writeln('<info>Operation canceled.</info>');
            return self::DEFAULT_ERROR;
        }
        $project = new Project($dir);

        if (!$this->report($output, $project)) {
            return self::DEFAULT_ERROR;
        }
        $env = $input->getOption('env');
        if ($env === 'f') {
            $env = self::FLEXIBLE_ENV;
        } elseif ($env === 's') {
            $env = self::STANDARD_ENV;
        } else {
            $q = new ChoiceQuestion(
                'Please select the App Engine Environment '
                . '(defaults to ' . self::FLEXIBLE_ENV  . ')',
                array(self::FLEXIBLE_ENV, self::STANDARD_ENV),
                self::FLEXIBLE_ENV
            );
            $q->setErrorMessage('Environment %s is invalid.');
            $env = $helper->ask($input, $output, $q);
        }
        if ($env === self::STANDARD_ENV) {
            $output->writeln(
                '<error>' . self::STANDARD_ENV
                . ' is not supported yet.</error>');
            return self::DEFAULT_ERROR;
        }
        $output->writeln('Creating a new project for: ' . $env);

        $output->writeln('Downloading the latest WordPress...');
        $project->downloadArchive(
            'the latest WordPress', self::LATEST_WP);
        if (!$this->report($output, $project)) {
            return self::DEFAULT_ERROR;
        }

        $output->writeln('Downloading the Batcache plugin...');
        $project->downloadArchive(
            'Batcache plugin', self::LATEST_BATCACHE,
            '/wordpress/wp-content/plugins'
        );
        if (!$this->report($output, $project)) {
            return self::DEFAULT_ERROR;
        }

        $output->writeln('Downloading the Memcached plugin...');
        $project->downloadArchive(
            'Memcached plugin', self::LATEST_MEMCACHED,
            '/wordpress/wp-content/plugins'
        );
        if (!$this->report($output, $project)) {
            return self::DEFAULT_ERROR;
        }

        $params = array();
        foreach ($configKeys as $key => $default) {
            $value = $input->getOption($key);
            if ((!$input->isInteractive()) && empty($value)) {
                $output->writeln(
                    '<error>' . $key . ' can not be empty.</error>');
                return self::DEFAULT_ERROR;
            }
            while (empty($value)) {
                if (empty($default)) {
                    $note = ' (mandatory input)';
                } else {
                    $note = ' (defaults to \'' . $default . '\')';
                }
                $q = new Question(
                    'Please enter ' . $key . $note . ': ', $default);
                if ($key === 'db_password') {
                    $q->setHidden(true);
                    $q->setHiddenFallback(false);
                }
                $value = $helper->ask($input, $output, $q);
                if (empty($value)) {
                    $output->writeln(
                        '<error>' . $key . ' can not be empty.</error>');
                }
            }
            $params[$key] = $value;
        }
        foreach ($authKeys as $key) {
            $value = Utils::createRandomKey();
            $params[$key] = $value;
        }
        $project->copyFiles($copyFiles, $params);
        if (!$this->report($output, $project)) {
            return self::DEFAULT_ERROR;
        }
        $project->runComposer();
        if (!$this->report($output, $project)) {
            return self::DEFAULT_ERROR;
        }
        $output->writeln(
            '<info>Your WordPress project is ready at '
            . $project->getDir() . '</info>'
        );
        return 0;
    }
}
