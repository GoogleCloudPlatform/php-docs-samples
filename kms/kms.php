<?php
/**
 * Copyright 2017 Google Inc.
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

namespace Google\Cloud\Samples\Kms;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;

$application = new Application('Google Cloud Key Management Store (KMS)');
$application->setDispatcher($dispatcher = new EventDispatcher());
$dispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) {
    $input = $event->getInput();
    // Try to get the default project ID from gcloud
    if ($input->hasOption('project') && !$input->getOption('project')) {
        exec(
            "gcloud config list --format 'value(core.project)' 2>/dev/null",
            $output,
            $return_var
        );

        if (0 !== $return_var) {
            throw new \Exception('Could not derive a project ID from gcloud. ' .
                'You must supply a project ID using --project');
        }

        $input->setOption('project', array_pop($output));
    }
});

$inputDefinition = new InputDefinition([
    new InputOption(
        'project',
        'p',
        InputOption::VALUE_REQUIRED,
        'The Google Cloud Platform project name to use for this invocation. ' .
        'If omitted then the current gcloud project is assumed. '
    ),
    new InputOption(
        'location',
        null,
        InputOption::VALUE_REQUIRED,
        'The location of the cryptokey or keyring.',
        'global'
    ),
]);

// Add Encryption Command
$application->add((new Command('encryption'))
    ->setDescription('Manage encryption for KMS')
    ->setDefinition(clone $inputDefinition)
    ->addArgument('keyring', InputArgument::REQUIRED, 'The name of the keyring.')
    ->addArgument('cryptokey', InputArgument::REQUIRED, 'The name of the cryptokey.')
    ->addArgument('infile', InputArgument::REQUIRED, 'The target file.')
    ->addArgument('outfile', InputArgument::REQUIRED, 'The file to store the result.')
    ->addOption('decrypt', null, InputOption::VALUE_NONE, 'Performs the decrypt function instead of encrypt. ')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command uses the KMS API to encrypt and decrypt text in files.

Encrypt the text of a file using the specified CryptoKey:

    <info>php %command.full_name% my-keyring my-cryptokey file.txt file.txt.encrypted</info>

Decrypt the text of a file using the specified CryptoKey:

    <info>php %command.full_name% my-keyring my-cryptokey file.txt.encrypted file.txt.decrypted --decrypt</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $keyRing = $input->getArgument('keyring');
        $cryptoKey = $input->getArgument('cryptokey');
        $infile = $input->getArgument('infile');
        $outfile = $input->getArgument('outfile');
        $location = $input->getOption('location');
        if ($input->getOption('decrypt')) {
            decrypt($projectId, $keyRing, $cryptoKey, $infile, $outfile, $location);
        } else {
            encrypt($projectId, $keyRing, $cryptoKey, $infile, $outfile, $location);
        }
    })
);

// Add IAM Command
$application->add((new Command('iam'))
    ->setDescription('Manage IAM for KMS')
    ->setDefinition(clone $inputDefinition)
    ->addArgument('keyring', InputArgument::REQUIRED, 'The name of the keyring.')
    ->addArgument('cryptokey', InputArgument::OPTIONAL, 'The name of the cryptokey.')
    ->addOption('service-account-email', null, InputOption::VALUE_REQUIRED, 'The service accunt email to add to the policy.')
    ->addOption('user-email', null, InputOption::VALUE_REQUIRED, 'The user email to add to the policy.')
    ->addOption('role', null, InputOption::VALUE_REQUIRED, 'The role of the policy.', 'roles/cloudkms.cryptoKeyEncrypterDecrypter')
    ->addOption('remove', null, InputOption::VALUE_NONE, 'If supplied, will remove the user or service account from the policy')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages KMS IAM policies.

List the IAM roles for a KeyRing:

    <info>php %command.full_name% my-keyring</info>

List the IAM roles for a CryptoKey:

    <info>php %command.full_name% my-keyring my-cryptokey</info>

Add a service account to a CryptoKey:

    <info>php %command.full_name% my-keyring my-cryptokey \
        --service-account-email=example@my-project.gserviceaccount.com</info>

Add a service account to a CryptoKey for a specific role:

    <info>php %command.full_name% my-keyring my-cryptokey \
        --service-account-email=example@my-project.gserviceaccount.com \
        --role=roles/cloudkms.admin</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $keyRing = $input->getArgument('keyring');
        $cryptoKey = $input->getArgument('cryptokey');
        $role = $input->getOption('role');
        $location = $input->getOption('location');
        $userEmail = $input->getOption('user-email');
        $serviceAccountEmail = $input->getOption('service-account-email');
        if ($cryptoKey) {
            if (empty($userEmail) && empty($serviceAccountEmail)) {
                get_cryptokey_policy($projectId, $keyRing, $cryptoKey, $location);
            } else {
                if ($userEmail) {
                    $member = 'user:' . $userEmail;
                } else {
                    $member = 'serviceAccount:' . $serviceAccountEmail;
                }
                if ($input->getOption('remove')) {
                    remove_member_from_cryptokey_policy($projectId, $keyRing, $cryptoKey, $member, $role, $location);
                } else {
                    add_member_to_cryptokey_policy($projectId, $keyRing, $cryptoKey, $member, $role, $location);
                }
            }
        } else {
            if (empty($userEmail) && empty($serviceAccountEmail)) {
                get_keyring_policy($projectId, $keyRing, $location);
            } else {
                if ($userEmail) {
                    $member = 'user:' . $userEmail;
                } else {
                    $member = 'serviceAccount:' . $serviceAccountEmail;
                }
                if ($input->getOption('remove')) {
                    remove_member_from_keyring_policy($projectId, $keyRing, $member, $role, $location);
                } else {
                    add_member_to_keyring_policy($projectId, $keyRing, $member, $role, $location);
                }
            }
        }
    })
);

// Add Key Command
$application->add((new Command('key'))
    ->setDescription('Manage keys for KMS')
    ->setDefinition(clone $inputDefinition)
    ->addArgument('keyring', InputArgument::REQUIRED, 'The name of the keyring.')
    ->addArgument('cryptokey', InputArgument::OPTIONAL, 'The name of the cryptokey.')
    ->addOption('create', null, InputOption::VALUE_NONE, 'If supplied, will create the keyring, cryptokey, or cryptokey version')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages KMS keys.

List all CrytoKeys for the supplied KeyRing:

    <info>php %command.full_name% my-keyring</info>

Display information about a CrytoKey:

    <info>php %command.full_name% my-keyring my-cryptokey</info>

Create a CrytoKey:

    <info>php %command.full_name% my-keyring new-cryptokey --create</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $keyRing = $input->getArgument('keyring');
        $cryptoKey = $input->getArgument('cryptokey');
        $location = $input->getOption('location');

        if ($cryptoKey) {
            if ($input->getOption('create')) {
                create_cryptokey($projectId, $keyRing, $cryptoKey, $location);
            } else {
                get_cryptokey($projectId, $keyRing, $cryptoKey, $location);
            }
        } else {
            list_cryptokeys($projectId, $keyRing, $location);
        }
    })
);

// Add KeyRing Command
$application->add((new Command('keyring'))
    ->setDescription('Manage keyrings for KMS')
    ->setDefinition(clone $inputDefinition)
    ->addArgument('keyring', InputArgument::OPTIONAL, 'The name of the keyring.')
    ->addOption('create', null, InputOption::VALUE_NONE, 'If supplied, will create the keyring, cryptokey, or cryptokey version')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages KMS keyrings.

List all KeyRings for a project:

    <info>php %command.full_name%</info>

Display information about a KeyRing:

    <info>php %command.full_name% my-keyring</info>

Create a KeyRing:

    <info>php %command.full_name% new-keyring --create</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $ring = $input->getArgument('keyring');
        $location = $input->getOption('location');
        if ($ring) {
            if ($input->getOption('create')) {
                create_keyring($projectId, $ring, $location);
            } else {
                get_keyring($projectId, $ring, $location);
            }
        } else {
            list_keyrings($projectId, $location);
        }
    })
);

// Add Version Command
$application->add((new Command('version'))
    ->setDescription('Manage key versions for KMS')
    ->setDefinition(clone $inputDefinition)
    ->addArgument('keyring', InputArgument::REQUIRED, 'The name of the keyring.')
    ->addArgument('cryptokey', InputArgument::REQUIRED, 'The name of the cryptokey.')
    ->addArgument('version', InputArgument::OPTIONAL, 'The version of the cryptokey.')
    ->addOption('create', null, InputOption::VALUE_NONE, 'If supplied, will create the keyring, cryptokey, or cryptokey version')
    ->addOption('destroy', null, InputOption::VALUE_NONE, 'If supplied, will destroy the cryptokey version')
    ->addOption('disable', null, InputOption::VALUE_NONE, 'If supplied, will disable the cryptokey version')
    ->addOption('enable', null, InputOption::VALUE_NONE, 'If supplied, will enable the cryptokey version')
    ->addOption('restore', null, InputOption::VALUE_NONE, 'If supplied, will restore the cryptokey version')
    ->addOption('set-primary', null, InputOption::VALUE_NONE, 'If supplied, will disable the cryptokey version')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages KMS key versions.

List all versions of a CryptoKey:

    <info>php %command.full_name% my-keyring my-cryptokey</info>

Display information about a specific CryptoKey version:

    <info>php %command.full_name% my-keyring my-cryptokey 1</info>

Create a new CryptoKey version:

    <info>php %command.full_name% my-keyring my-cryptokey --create</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $keyRing = $input->getArgument('keyring');
        $cryptoKey = $input->getArgument('cryptokey');
        $cryptoKeyVersion = $input->getArgument('version');
        $location = $input->getOption('location');
        if ($input->getOption('create')) {
            create_cryptokey_version($projectId, $keyRing, $cryptoKey, $location);
        } elseif ($cryptoKeyVersion) {
            if ($input->getOption('destroy')) {
                destroy_cryptokey_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            } elseif ($input->getOption('disable')) {
                disable_cryptokey_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            } elseif ($input->getOption('restore')) {
                restore_cryptokey_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            } elseif ($input->getOption('enable')) {
                enable_cryptokey_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            } elseif ($input->getOption('set-primary')) {
                set_cryptokey_primary_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            } else {
                get_cryptokey_version($projectId, $keyRing, $cryptoKey, $cryptoKeyVersion, $location);
            }
        } else {
            list_cryptokey_versions($projectId, $keyRing, $cryptoKey, $location);
        }
    })
);

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
