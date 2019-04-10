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
namespace Google\Cloud\Samples\Iot;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$application = new Application('Cloud IOT');

$application->add(new Command('list-devices'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registry', 'us-central1')
    ->setDescription('List all devices in the registry.')
    ->setCode(function ($input, $output) {
        list_devices(
            $input->getArgument('registry'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('list-registries'))
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('List all registries in the project.')
    ->setCode(function ($input, $output) {
        list_registries(
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('create-registry'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('pubsub-topic', InputArgument::REQUIRED, 'PubSub topic name for the new registry\'s event change notification.')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Creates a registry and returns the result.')
    ->setCode(function ($input, $output) {
        create_registry(
            $input->getArgument('registry'),
            $input->getArgument('pubsub-topic'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('delete-registry'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Deletes the specified registry.')
    ->setCode(function ($input, $output) {
        delete_registry(
            $input->getArgument('registry'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('create-unauth-device'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Create a new device without authentication.')
    ->setCode(function ($input, $output) {
        create_unauth_device(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('create-es-device'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addArgument('public-key-file', InputArgument::REQUIRED, 'Path to public ES256 key file')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Create a new device with the given id, using ES256 for authentication.')
    ->setCode(function ($input, $output) {
        create_es_device(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getArgument('public-key-file'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('create-rsa-device'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addArgument('certificate-file', InputArgument::REQUIRED, 'Path to public RS256 key file')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Create a new device with the given id, using RS256 for authentication.')
    ->setCode(function ($input, $output) {
        create_rsa_device(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getArgument('certificate-file'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('delete-device'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Delete the device with the given id.')
    ->setCode(function ($input, $output) {
        delete_device(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('get-device'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Retrieve the device with the given id.')
    ->setCode(function ($input, $output) {
        get_device(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('get-registry'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Retrieves a device registry.')
    ->setCode(function ($input, $output) {
        get_registry(
            $input->getArgument('registry'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('get-device-configs'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Lists versions of a device config in descending order (newest first).')
    ->setCode(function ($input, $output) {
        get_device_configs(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('get-device-state'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Retrieve a device\'s state blobs.')
    ->setCode(function ($input, $output) {
        get_device_state(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('patch-es-device'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addArgument('public-key-file', InputArgument::REQUIRED, 'Path to public ES256 key file')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Patch device with ES256 public key.')
    ->setCode(function ($input, $output) {
        patch_es(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getArgument('public-key-file'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('patch-rsa-device'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addArgument('certificate-file', InputArgument::REQUIRED, 'Path to public RS256 key file')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Patch device with RSA256 certificate.')
    ->setCode(function ($input, $output) {
        patch_rsa(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getArgument('certificate-file'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('set-device-config'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addArgument('config', InputArgument::REQUIRED, 'Configuration sent to a device')
    ->addArgument('version', InputArgument::OPTIONAL, 'Version number for setting device configuration. Defaults to current version')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Set a device\'s configuration.')
    ->setCode(function ($input, $output) {
        set_device_config(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getArgument('config'),
            $input->getArgument('version'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('get-iam-policy'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Retrieves IAM permissions for the given registry.')
    ->setCode(function ($input, $output) {
        get_iam_policy(
            $input->getArgument('registry'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('set-iam-policy'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('role', InputArgument::REQUIRED, 'the IAM role (ex: roles/viewer)')
    ->addArgument('member', InputArgument::REQUIRED, 'the IAM member (ex: user:you@gmail.com)')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Sets IAM permissions for the given registry to a single role/member.')
    ->setCode(function ($input, $output) {
        set_iam_policy(
            $input->getArgument('registry'),
            $input->getArgument('role'),
            $input->getArgument('member'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('send-command-to-device'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addArgument('command-data', InputArgument::REQUIRED, 'the binary data to send as the command')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Sends a command to a device.')
    ->setCode(function ($input, $output) {
        send_command_to_device(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getArgument('command-data'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

$application->add(new Command('set-device-state'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addArgument('certificate-file', InputArgument::REQUIRED, 'Path to public RS256 key file')
    ->addArgument('state-data', InputArgument::REQUIRED, 'the binary data to set for the device state')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('Sets the state of a device.')
    ->setCode(function ($input, $output) {
        set_device_state(
            $input->getArgument('registry'),
            $input->getArgument('device'),
            $input->getArgument('certificate-file'),
            $input->getArgument('state-data'),
            $input->getOption('project'),
            $input->getOption('location')
        );
    });

// Beta features
$application->add(new Command('create-gateway'))
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('gateway', InputArgument::REQUIRED, 'the gateway ID')
    ->addArgument('certificate-file', InputArgument::REQUIRED, 'Path to public key file')
    ->addArgument('algorithm', InputArgument::REQUIRED, 'The algorithm (RS256|ES256) used for the public key')
    ->setDescription('(Beta feature) Create a new gateway with the given id.')
    ->setCode(function ($input, $output) {
        create_gateway(
            $input->getOption('project'),
            $input->getOption('location'),
            $input->getArgument('registry'),
            $input->getArgument('gateway'),
            $input->getArgument('certificate-file'),
            $input->getArgument('algorithm')
        );
    });

$application->add(new Command('delete-gateway'))
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('gateway', InputArgument::REQUIRED, 'the gateway ID')
    ->setDescription('(Beta feature) Delete the gateway with the given id.')
    ->setCode(function ($input, $output) {
        delete_gateway(
            $input->getOption('project'),
            $input->getOption('location'),
            $input->getArgument('registry'),
            $input->getArgument('gateway')
        );
    });

$application->add(new Command('list-gateways'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('(Beta feature) List gateways for the given registry.')
    ->setCode(function ($input, $output) {
        list_gateways(
            $input->getOption('project'),
            $input->getOption('location'),
            $input->getArgument('registry')
        );
    });

$application->add(new Command('list-devices-for-gateway'))
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('gateway', InputArgument::REQUIRED, 'the gateway ID')
    ->setDescription('(Beta feature) List devices for the given gateway.')
    ->setCode(function ($input, $output) {
        list_devices_for_gateway(
            $input->getOption('project'),
            $input->getOption('location'),
            $input->getArgument('registry'),
            $input->getArgument('gateway')
        );
    });

$application->add(new Command('bind-device-to-gateway'))
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addArgument('gateway', InputArgument::REQUIRED, 'the gateway ID')
    ->setDescription('(Beta feature) Bind a device to a gateway.')
    ->setCode(function ($input, $output) {
        bind_device_to_gateway(
            $input->getOption('project'),
            $input->getOption('location'),
            $input->getArgument('registry'),
            $input->getArgument('gateway'),
            $input->getArgument('device')
        );
    });

$application->add(new Command('unbind-device-from-gateway'))
    ->addArgument('registry', InputArgument::REQUIRED, 'the registry ID')
    ->addArgument('device', InputArgument::REQUIRED, 'the device ID')
    ->addArgument('gateway', InputArgument::REQUIRED, 'the gateway ID')
    ->addOption('project', '', InputOption::VALUE_REQUIRED, 'The Google Cloud project ID', getenv('GCLOUD_PROJECT'))
    ->addOption('location', '', InputOption::VALUE_REQUIRED, 'The location of your device registries', 'us-central1')
    ->setDescription('(Beta feature) Unbind a device from a gateway.')
    ->setCode(function ($input, $output) {
        unbind_device_from_gateway(
            $input->getOption('project'),
            $input->getOption('location'),
            $input->getArgument('registry'),
            $input->getArgument('gateway'),
            $input->getArgument('device')
        );
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
