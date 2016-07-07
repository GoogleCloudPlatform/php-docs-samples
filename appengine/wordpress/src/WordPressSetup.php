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
        'https://downloads.wordpress.org/plugin/memcached.3.0.1.zip';
    const LATEST_GAE_WP =
        'https://downloads.wordpress.org/plugin/google-app-engine.1.6.zip';

    const FLEXIBLE_ENV = 'Flexible Environment';
    const STANDARD_ENV = 'Standard Environment';

    const DEFAULT_DB_REGION = 'us-central1';

    private static $availableDbRegions = array(
        'us-central1',
        'us-east1',
        'europe-west1',
        'asia-east1',
    );

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
                'sql_gen',
                '',
                InputOption::VALUE_OPTIONAL,
                sprintf('Cloud SQL generation to use; 2: %s, 1: %s',
                        'Second Generation',
                        'First Generation'),
                2
            )
            ->addOption(
                'project_id',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Google Cloud project id',
                ''
            )
            ->addOption(
                'db_region',
                null,
                InputOption::VALUE_OPTIONAL,
                'Cloud SQL region',
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
            )
            ->addOption(
                'local_db_user',
                null,
                InputOption::VALUE_OPTIONAL,
                'Local SQL database username',
                ''
            )
            ->addOption(
                'local_db_password',
                null,
                InputOption::VALUE_OPTIONAL,
                'Local SQL database password',
                ''
            )
            ->addOption(
                'wordpress_url',
                null,
                InputOption::VALUE_OPTIONAL,
                'URL of the WordPress archive',
                self::LATEST_WP
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

    protected function addAuthKeys(&$params)
    {
        $authKeys = array(
            'auth_key', 'secure_auth_key', 'logged_in_key', 'nonce_key',
            'auth_salt', 'secure_auth_salt', 'logged_in_salt', 'nonce_salt'
        );
        foreach ($authKeys as $key) {
            $value = Utils::createRandomKey();
            $params[$key] = $value;
        }
    }

    protected function askParameters(
        array $configKeys,
        array &$params,
        InputInterface $input,
        OutputInterface $output,
        $helper
    ) {
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
                if (strpos($key, 'password') !== false) {
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
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
                . '(defaults to ' . self::FLEXIBLE_ENV . ')',
                array(self::FLEXIBLE_ENV, self::STANDARD_ENV),
                self::FLEXIBLE_ENV
            );
            $q->setErrorMessage('Environment %s is invalid.');
            $env = $helper->ask($input, $output, $q);
        }
        $output->writeln('Creating a new project for: <info>' . $env
                         . '</info>');

        // Determine the Cloud SQL Generation to use.
        $sql_gen = $input->getOption('sql_gen');
        switch ($sql_gen) {
            case '1':
                if ($env === self::FLEXIBLE_ENV) {
                    $output->writeln('<error>You can not use '
                                     . 'Cloud SQL First Generation with '
                                     . self::FLEXIBLE_ENV . '.</error>');
                    return self::DEFAULT_ERROR;
                }
                $db_connection_pattern = '%s:%s';
                break;
            case '2':
                $db_region = $input->getOption('db_region');
                if (! in_array($db_region, self::$availableDbRegions)) {
                    $q = new ChoiceQuestion(
                        'Please select the region of your Cloud SQL instance '
                        . '(defaults to ' . self::DEFAULT_DB_REGION . ')',
                        self::$availableDbRegions,
                        self::DEFAULT_DB_REGION
                    );
                    $q->setErrorMessage('DB region %s is invalid.');
                    $db_region = $helper->ask($input, $output, $q);
                    $output->writeln('Using a db_region: <info>' . $db_region
                                     . '</info>');
                }
                $db_connection_pattern = "%s:$db_region:%s";
                break;
            default:
                $output->writeln(
                    sprintf(
                        '<error>Invalid value for sql_gen: %s.</error>',
                        $sql_gen
                    )
                );
                return self::DEFAULT_ERROR;
        }

        $output->writeln('Downloading the WordPress archive...');
        $wpUrl = $input->getOption('wordpress_url');
        $project->downloadArchive('the WordPress archive', $wpUrl);
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

        $output->writeln('Copying drop-ins...');
        $dir = $project->getDir();
        copy(
            $dir . '/wordpress/wp-content/plugins/batcache/advanced-cache.php',
            $dir . '/wordpress/wp-content/advanced-cache.php'
        );
        copy(
            $dir . '/wordpress/wp-content/plugins/memcached/object-cache.php',
            $dir . '/wordpress/wp-content/object-cache.php'
        );

        $keys = array(
            'project_id' => '',
            'db_instance' => 'wp',
            'db_name' => 'wp',
            'db_user' => 'wp',
            'db_password' => '',
        );
        if ($env === self::STANDARD_ENV) {
            $copyFiles = array(
                'app.yaml' => '/',
                'cron.yaml' => '/',
                'composer.json' => '/',
                'php.ini' => '/',
                'wp-config.php' => '/wordpress/',
            );
            $templateDir = __DIR__ . '/files/standard';
            $output->writeln('Downloading the appengine-wordpress plugin...');
            $project->downloadArchive(
                'App Engine WordPress plugin', self::LATEST_GAE_WP,
                '/wordpress/wp-content/plugins'
            );
            if (!$this->report($output, $project)) {
                return self::DEFAULT_ERROR;
            }
        } else {
            $copyFiles = array(
                'app.yaml' => '/',
                'cron.yaml' => '/',
                'composer.json' => '/',
                'gcs-media.php' => '/wordpress/wp-content/plugins/',
                'nginx-app.conf' => '/',
                'php.ini' => '/',
                'wp-config.php' => '/wordpress/',
            );
            $templateDir = __DIR__ . '/files/flexible';
        }
        $params = array();
        $this->askParameters($keys, $params, $input, $output, $helper);
        $params['db_connection'] = sprintf(
            $db_connection_pattern,
            $params['project_id'],
            $params['db_instance']
        );
        $q = new ConfirmationQuestion(
            'Do you want to use the same db user and password for '
            . 'local run? (Y/n)',
            true
        );
        if ($helper->ask($input, $output, $q)) {
            $params['local_db_user'] = $params['db_user'];
            $params['local_db_password'] = $params['db_password'];
        } else {
            $keys = array(
                'local_db_user' => 'wp',
                'local_db_password' => '',
            );
            $this->askParameters($keys, $params, $input, $output, $helper);
        }
        $this->addAuthKeys($params);
        $project->copyFiles($templateDir, $copyFiles, $params);
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
