<?php
/**
 * Copyright 2017 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\ErrorReporting;

use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Stackdriver Error Reporting');

$inputDefinition = new InputDefinition([
    new InputArgument('project_id', InputArgument::REQUIRED, 'The project id'),
    new InputArgument('message', InputArgument::OPTIONAL, 'The error message', 'My Error Message'),
]);

$application->add(new Command('report-simple'))
    ->setDefinition(clone $inputDefinition)
    ->setDescription('Reports a simple error message.')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project_id');
        $message = $input->getArgument('message');
        require_once __DIR__ . '/src/report_error_simple.php';
    });

$application->add(new Command('report'))
    ->setDefinition(clone $inputDefinition)
    ->addOption(
        'user',
        '',
        InputOption::VALUE_REQUIRED,
        'The user attributed to the error.',
        'test@user.com'
    )
    ->addOption(
        'service',
        '',
        InputOption::VALUE_REQUIRED,
        'The service where the error occurred.',
        'service'
    )
    ->addOption(
        'app-version',
        '',
        InputOption::VALUE_REQUIRED,
        'The version for which the error occurred.',
        'version'
    )
    ->setDescription('Reports an error message with context and user data.')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project_id');
        $message = $input->getArgument('message');
        $user = $input->getOption('user');
        $service = $input->getOption('service');
        $version = $input->getOption('app-version');
        require_once __DIR__ . '/src/report_error_manually.php';
    });

$application->add(new Command('report-grpc'))
    ->setDefinition(clone $inputDefinition)
    ->setDescription('Reports a custom error object using gRPC.')
    ->addOption(
        'user',
        '',
        InputOption::VALUE_REQUIRED,
        'The user attributed to the error.',
        'test@user.com'
    )
    ->addOption(
        'with-stacktrace',
        '',
        InputOption::VALUE_NONE,
        'Include a stack trace in the error.'
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project_id');
        $message = $input->getArgument('message');
        $user = $input->getOption('user');
        if ($input->getOption('with-stacktrace')) {
            # [START message-to-exception]
            $message = (string) new Exception($message);
            # [END message-to-exception]
        }
        require_once __DIR__ . '/src/report_error_grpc.php';
    });

$application->add(new Command('test-exception-handler'))
    ->setDescription('Reports an exception using an exception handler.')
    ->addArgument(
        'project_id',
        InputArgument::REQUIRED,
        'Type of error to test: "exception", "error", or "fatal".'
    )
    ->addArgument(
        'message',
        InputArgument::OPTIONAL,
        'The error message.'
    )
    ->addOption(
        'type',
        '',
        InputOption::VALUE_REQUIRED,
        'Type of error to test: "exception", "error", or "fatal".',
        'exception'
    )
    ->addOption(
        'service',
        '',
        InputOption::VALUE_REQUIRED,
        'The service where the error occurred.',
        'service'
    )
    ->addOption(
        'app-version',
        '',
        InputOption::VALUE_REQUIRED,
        'The version for which the error occurred.',
        'version'
    )
    ->setCode(function ($input, $output) use ($application) {
        $errorType = $input->getOption('type');
        if (!in_array($errorType, ['exception', 'error', 'fatal'])) {
            throw new \InvalidArgumentException('Invalid error type provided, '
                . 'must be one one of "exception", "error", or "fatal".');
        }
        $projectId = $input->getArgument('project_id');
        $service = $input->getOption('service');
        $version = $input->getOption('app-version');
        require_once __DIR__ . '/src/register_exception_handler.php';

        // disable Console Application exception handlers
        $application->setCatchExceptions(false);

        // throw a test exception to trigger our exception handler
        $message = $input->getArgument('message');
        switch ($errorType) {
            case 'exception':
                print('Throwing a PHP Exception...' . PHP_EOL);
                throw new \Exception($message ?: 'This is from "throw new Exception()"');
            case 'fatal':
                print('Triggering a PHP Fatal Error by eval-ing a syntax error...' . PHP_EOL);
                eval('syntax-error');
                break;
            case 'error':
                print('Triggering a PHP Error' . PHP_EOL);
                trigger_error($message ?: 'This is from "trigger_error()"', E_USER_ERROR);
        }
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
