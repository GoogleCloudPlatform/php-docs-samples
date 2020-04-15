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

namespace Google\Cloud\Samples\Storage;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use InvalidArgumentException;

$application = new Application();

// Create Bucket ACL command
$application->add(new Command('bucket-acl'))
    ->setDescription('Manage the ACL for Cloud Storage buckets.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage ACL.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'Add or filter by a user')
    ->addOption('role', null, InputOption::VALUE_REQUIRED, 'One of OWNER, READER, or WRITER', 'READER')
    ->addOption('create', null, InputOption::VALUE_NONE, 'Create an ACL for the supplied user')
    ->addOption('delete', null, InputOption::VALUE_NONE, 'Remove a user from the ACL')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        $entity = $input->getOption('entity');
        $role = $input->getOption('role');
        if ($entity) {
            if ($input->getOption('create')) {
                add_bucket_acl($bucketName, $entity, $role);
            } elseif ($input->getOption('delete')) {
                delete_bucket_acl($bucketName, $entity);
            } else {
                get_bucket_acl_for_entity($bucketName, $entity);
            }
        } else {
            get_bucket_acl($bucketName);
        }
    });

// Create Bucket Default ACL command
$application->add(new Command('bucket-default-acl'))
    ->setDescription('Manage the default ACL for Cloud Storage buckets.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage ACL.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'Add or filter by a user')
    ->addOption('role', null, InputOption::VALUE_REQUIRED, 'One of OWNER, READER, or WRITER', 'READER')
    ->addOption('create', null, InputOption::VALUE_NONE, 'Create an ACL for the supplied user')
    ->addOption('delete', null, InputOption::VALUE_NONE, 'Remove a user from the ACL')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        $entity = $input->getOption('entity');
        $role = $input->getOption('role');
        if ($entity) {
            if ($input->getOption('create')) {
                add_bucket_default_acl($bucketName, $entity, $role);
            } elseif ($input->getOption('delete')) {
                delete_bucket_default_acl($bucketName, $entity);
            } else {
                get_bucket_default_acl_for_entity($bucketName, $entity);
            }
        } else {
            get_bucket_default_acl($bucketName);
        }
    });

// Create Bucket Labels command
$application->add(new Command('bucket-labels'))
    ->setDescription('Manage Cloud Storage bucket labels')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage Bucket labels.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addArgument('label', InputArgument::OPTIONAL, 'The Cloud Storage label')
    ->addOption('value', null, InputOption::VALUE_REQUIRED, 'Set the value of the label')
    ->addOption('remove', null, InputOption::VALUE_NONE, 'Remove the buckets label')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        if ($label = $input->getArgument('label')) {
            if ($value = $input->getOption('value')) {
                add_bucket_label($bucketName, $label, $value);
            } elseif ($input->getOption('remove')) {
                remove_bucket_label($bucketName, $label);
            } else {
                throw new \Exception('You must provide --value or --remove '
                    . 'when including a label name.');
            }
        } else {
            get_bucket_labels($bucketName);
        }
    });

// Create Buckets command
$application->add(new Command('buckets'))
    ->setDescription('Manage Cloud Storage buckets')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages buckets.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::OPTIONAL, 'The Cloud Storage bucket name')
    ->addOption('create', null, InputOption::VALUE_NONE, 'Create the bucket')
    ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete the bucket')
    ->addOption('metadata', null, InputOption::VALUE_NONE, 'Get the bucket metadata')
    ->setCode(function ($input, $output) {
        if ($bucketName = $input->getArgument('bucket')) {
            if ($input->getOption('create')) {
                create_bucket($bucketName);
            } elseif ($input->getOption('delete')) {
                delete_bucket($bucketName);
            } elseif ($input->getOption('metadata')) {
                get_bucket_metadata($bucketName);
            } else {
                throw new \Exception('Supply --create or --delete with bucket name');
            }
        } else {
            list_buckets();
        }
    });


// Set Bucket Lock commands
$application->add(new Command('bucket-lock'))
    ->setDescription('Manage Cloud Storage retention policies')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage retention policies.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addArgument('object', InputArgument::OPTIONAL, 'The Cloud Storage object name')
    ->addArgument('retention-period', InputArgument::OPTIONAL, 'The length of the retention period in seconds')
    ->addOption('set-retention-policy', null, InputOption::VALUE_NONE, 'Set the retention policy')
    ->addOption('remove-retention-policy', null, InputOption::VALUE_NONE, 'Remove the retention policy')
    ->addOption('lock-retention-policy', null, InputOption::VALUE_NONE, 'Lock the retention policy')
    ->addOption('get-retention-policy', null, InputOption::VALUE_NONE, 'Gets the retention policy')
    ->addOption('set-event-based-hold', null, InputOption::VALUE_NONE, 'Set an event-based hold')
    ->addOption('release-event-based-hold', null, InputOption::VALUE_NONE, 'Release an event-based hold')
    ->addOption('enable-default-event-based-hold', null, InputOption::VALUE_NONE, 'Enable default event-based hold')
    ->addOption('disable-default-event-based-hold', null, InputOption::VALUE_NONE, 'Disable default event-based hold')
    ->addOption('get-default-event-based-hold', null, InputOption::VALUE_NONE, 'Gets default event-based hold')
    ->addOption('set-temporary-hold', null, InputOption::VALUE_NONE, 'Set a temporary hold')
    ->addOption('release-temporary-hold', null, InputOption::VALUE_NONE, 'Release a temporary hold')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        if ($bucketName) {
            if ($input->getOption('remove-retention-policy')) {
                remove_retention_policy($bucketName);
            } elseif ($input->getOption('lock-retention-policy')) {
                lock_retention_policy($bucketName);
            } elseif ($input->getOption('get-retention-policy')) {
                get_retention_policy($bucketName);
            } elseif ($input->getOption('enable-default-event-based-hold')) {
                enable_default_event_based_hold($bucketName);
            } elseif ($input->getOption('disable-default-event-based-hold')) {
                disable_default_event_based_hold($bucketName);
            } elseif ($input->getOption('get-default-event-based-hold')) {
                get_default_event_based_hold($bucketName);
            } elseif ($input->getOption('set-retention-policy')) {
                if ($retentionPeriod = $input->getArgument('retention-period')) {
                    set_retention_policy($bucketName, $retentionPeriod);
                } else {
                    throw new \Exception('Supply a retention period');
                }
            } elseif ($objectName = $input->getArgument('object')) {
                if ($input->getOption('set-event-based-hold')) {
                    set_event_based_hold($bucketName, $objectName);
                } elseif ($input->getOption('release-event-based-hold')) {
                    release_event_based_hold($bucketName, $objectName);
                } elseif ($input->getOption('set-temporary-hold')) {
                    set_temporary_hold($bucketName, $objectName);
                } elseif ($input->getOption('release-temporary-hold')) {
                    release_temporary_hold($bucketName, $objectName);
                }
            } else {
                throw new \Exception('Supply an object name');
            }
        } else {
            throw new \Exception('Supply a bucket name');
        }
    });

// Create Encryption command
$application->add(new Command('encryption'))
    ->setDescription('Upload and download Cloud Storage objects with encryption')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage ACL.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::OPTIONAL, 'The Cloud Storage bucket name')
    ->addArgument('object', InputArgument::OPTIONAL, 'The Cloud Storage object name')
    ->addOption('upload-from', null, InputOption::VALUE_REQUIRED, 'Path to the file to upload')
    ->addOption('download-to', null, InputOption::VALUE_REQUIRED, 'Path to store the dowloaded file')
    ->addOption('key', null, InputOption::VALUE_REQUIRED, 'Supply your encryption key')
    ->addOption('rotate-key', null, InputOption::VALUE_REQUIRED, 'Supply a new encryption key')
    ->addOption('generate-key', null, InputOption::VALUE_NONE, 'Generates an encryption key')
    ->setCode(function ($input, $output) {
        if ($input->getOption('generate-key')) {
            generate_encryption_key();
        } else {
            $bucketName = $input->getArgument('bucket');
            $objectName = $input->getArgument('object');
            $encryptionKey = $input->getOption('key');
            if ($bucketName && $objectName) {
                if ($source = $input->getOption('upload-from')) {
                    upload_encrypted_object($bucketName, $objectName, $source, $encryptionKey);
                } elseif ($destination = $input->getOption('download-to')) {
                    download_encrypted_object($bucketName, $objectName, $destination, $encryptionKey);
                } elseif ($rotateKey = $input->getOption('rotate-key')) {
                    if (is_null($encryptionKey)) {
                        throw new \Exception('--key is required when using --rotate-key');
                    }
                    rotate_encryption_key($bucketName, $objectName, $encryptionKey, $rotateKey);
                } else {
                    throw new \Exception('Supply --rotate-key, --upload-from or --download-to');
                }
            } else {
                throw new \Exception('Supply a bucket and object OR --generate-key');
            }
        }
    });

$application->add(new Command('iam'))
    ->setDescription('Manage IAM for Storage')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Storage IAM policies.

<info>php %command.full_name% my-bucket</info>

<info>php %command.full_name% my-bucket --role my-role --add-member user:test1@email.com --add-member user:test2@email.com</info>

<info>php %command.full_name% my-bucket --role my-role --remove-member user:test@email.com</info>

<info>php %command.full_name% my-bucket --role my-role --remove-binding --title cond-title --description cond-description --expression cond-expression</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The bucket that you want to change IAM for. ')
    ->addOption('role', null, InputOption::VALUE_REQUIRED, 'The new role to add to a bucket. ')
    ->addOption('add-member', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, "The new member(s) to add with the new role to the bucket. ")
    ->addOption('remove-member', null, InputOption::VALUE_REQUIRED, 'The member to remove from a role for a bucket. ')
    ->addOption('remove-binding', null, InputOption::VALUE_NONE, 'Remove conditional policy')
    ->addOption('title', null, InputOption::VALUE_REQUIRED, 'Optional. A title for the condition, if --expression is used. ')
    ->addOption('description', null, InputOption::VALUE_REQUIRED, 'Optional. A description for the condition, if --expression is used. ')
    ->addOption('expression', null, InputOption::VALUE_REQUIRED, 'Add the role/members pair with an IAM condition expression. ')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        $role = $input->getOption('role');
        $members = $input->getOption('add-member');
        $removeMember = $input->getOption('remove-member');
        $removeBinding = $input->getOption('remove-binding');
        $expression = $input->getOption('expression');
        $title = $input->getOption('title');
        $description = $input->getOption('description');
        if ($members) {
            if (!$role) {
                throw new InvalidArgumentException('Must provide role as an option.');
            }

            if ($expression) {
                add_bucket_conditional_iam_binding($bucketName, $role, $members, $title, $description, $expression);
            } else {
                add_bucket_iam_member($bucketName, $role, $members);
            }
        } elseif ($removeMember) {
            if (!$role) {
                throw new InvalidArgumentException('Must provide role as an option.');
            }
            remove_bucket_iam_member($bucketName, $role, $removeMember);
        } elseif ($removeBinding) {
            if (!$role) {
                throw new InvalidArgumentException('Must provide role as an option.');
            }
            if (!$title) {
                throw new InvalidArgumentException('Must provide title as an option.');
            }
            if (!$description) {
                throw new InvalidArgumentException('Must provide description as an option.');
            }
            if (!$expression) {
                throw new InvalidArgumentException('Must provide expression as an option.');
            }
            remove_bucket_conditional_iam_binding($bucketName, $role, $title, $description, $expression);
        } else {
            view_bucket_iam_members($bucketName);
        }
    });

$application->add(new Command('object-acl'))
    ->setDescription('Manage the ACL for Cloud Storage objects')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage ACL.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addArgument('object', InputArgument::REQUIRED, 'The Cloud Storage object name')
    ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'Add or filter by a user')
    ->addOption('role', null, InputOption::VALUE_REQUIRED, 'One of OWNER, READER, or WRITER', 'READER')
    ->addOption('create', null, InputOption::VALUE_NONE, 'Create an ACL for the supplied user')
    ->addOption('delete', null, InputOption::VALUE_NONE, 'Remove a user from the ACL')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        $entity = $input->getOption('entity');
        $role = $input->getOption('role');
        $objectName = $input->getArgument('object');
        if ($entity) {
            if ($input->getOption('create')) {
                add_object_acl($bucketName, $objectName, $entity, $role);
            } elseif ($input->getOption('delete')) {
                delete_object_acl($bucketName, $objectName, $entity);
            } else {
                get_object_acl_for_entity($bucketName, $objectName, $entity);
            }
        } else {
            get_object_acl($bucketName, $objectName);
        }
    });

$application->add(new Command('objects'))
    ->setDescription('Manage Cloud Storage objects')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage objects.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addArgument('object', InputArgument::OPTIONAL, 'The Cloud Storage object name')
    ->addOption('upload-from', null, InputOption::VALUE_REQUIRED, 'Path to the file to upload')
    ->addOption('download-to', null, InputOption::VALUE_REQUIRED, 'Path to store the dowloaded file')
    ->addOption('move-to', null, InputOption::VALUE_REQUIRED, 'new name for the object')
    ->addOption('copy-to', null, InputOption::VALUE_REQUIRED, 'copy path for the object')
    ->addOption('make-public', null, InputOption::VALUE_NONE, 'makes the supplied object public')
    ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete the bucket')
    ->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'List objects matching a prefix')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        if ($objectName = $input->getArgument('object')) {
            if ($source = $input->getOption('upload-from')) {
                upload_object($bucketName, $objectName, $source);
            } elseif ($destination = $input->getOption('download-to')) {
                download_object($bucketName, $objectName, $destination);
            } elseif ($newObjectName = $input->getOption('move-to')) {
                move_object($bucketName, $objectName, $bucketName, $newObjectName);
            } elseif ($newObjectName = $input->getOption('copy-to')) {
                copy_object($bucketName, $objectName, $bucketName, $newObjectName);
            } elseif ($input->getOption('make-public')) {
                make_public($bucketName, $objectName);
            } elseif ($input->getOption('delete')) {
                delete_object($bucketName, $objectName);
            } else {
                object_metadata($bucketName, $objectName);
            }
        } else {
            if ($prefix = $input->getOption('prefix')) {
                list_objects_with_prefix($bucketName, $prefix);
            } else {
                list_objects($bucketName);
            }
        }
    });

$application->add(new Command('requester-pays'))
    ->setDescription('Manage Cloud Storage requester pays buckets.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage requester pays buckets.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('project', InputArgument::REQUIRED, 'Your billable Google Cloud Project ID')
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage requester pays bucket name')
    ->addArgument('object', InputArgument::OPTIONAL, 'The Cloud Storage requester pays object name')
    ->addArgument('download-to', null, InputArgument::OPTIONAL, 'Path to store the dowloaded file')
    ->addOption('enable', null, InputOption::VALUE_NONE, 'Enable requester pays on a Cloud Storage bucket')
    ->addOption('disable', null, InputOption::VALUE_NONE, 'Disable requester pays on a Cloud Storage bucket')
    ->addOption('check-status', null, InputOption::VALUE_NONE, 'Check requester pays status on a Cloud Storage bucekt')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $bucketName = $input->getArgument('bucket');
        if ($objectName = $input->getArgument('object')) {
            if ($destination = $input->getArgument('download-to')) {
                download_file_requester_pays($projectId, $bucketName, $objectName, $destination);
            }
        } elseif ($input->getOption('enable')) {
            enable_requester_pays($projectId, $bucketName);
        } elseif ($input->getOption('disable')) {
            disable_requester_pays($projectId, $bucketName);
        } elseif ($input->getOption('check-status')) {
            get_requester_pays_status($projectId, $bucketName);
        }
    });

$application->add(new Command('uniform-bucket-level-access'))
    ->setDescription('Manage Cloud Storage uniform bucket-level access buckets.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage uniform bucket-level access buckets.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage uniform bucket-level access bucket name')
    ->addOption('enable', null, InputOption::VALUE_NONE, 'Enable uniform bucket-level access on a Cloud Storage bucket')
    ->addOption('disable', null, InputOption::VALUE_NONE, 'Disable uniform bucket-level access on a Cloud Storage bucket')
    ->addOption('get', null, InputOption::VALUE_NONE, 'Get uniform bucket-level access on a Cloud Storage bucekt')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        if ($input->getOption('enable')) {
            enable_uniform_bucket_level_access($bucketName);
        } elseif ($input->getOption('disable')) {
            disable_uniform_bucket_level_access($bucketName);
        } elseif ($input->getOption('get')) {
            get_uniform_bucket_level_access($bucketName);
        } else {
            throw new \Exception('You must provide --enable, --disable, or --get with a bucket name.');
        }
    });

$application->add(new Command('hmac-sa-list'))
    ->setDescription('List Cloud Storage HMAC Keys.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command lists Cloud Storage HMAC Keys.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('projectId', InputArgument::REQUIRED, 'The Cloud Project ID with HMAC Keys to list')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('projectId');
        list_hmac_keys($projectId);
    });

$application->add(new Command('hmac-sa-create'))
    ->setDescription('Create a Cloud Storage HMAC Key.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates Cloud Storage HMAC Keys.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('projectId', InputArgument::REQUIRED, 'The Cloud Project ID associated with the service account email')
    ->addArgument('serviceAccountEmail', InputArgument::REQUIRED, 'The service account to associate with the new HMAC Key')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('projectId');
        $serviceAccountEmail = $input->getArgument('serviceAccountEmail');
        create_hmac_key($serviceAccountEmail, $projectId);
    });

$application->add(new Command('hmac-sa-manage'))
    ->setDescription('Manage Cloud Storage HMAC Keys.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command manages Cloud Storage HMAC Keys.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('projectId', InputArgument::REQUIRED, 'The Cloud Project ID associated with the HMAC Key')
    ->addArgument('accessId', InputArgument::REQUIRED, 'The Cloud Storage HMAC Key access ID')
    ->addOption('activate', null, InputOption::VALUE_NONE, 'Activate an HMAC Key')
    ->addOption('deactivate', null, InputOption::VALUE_NONE, 'Deactivate an HMAC Key')
    ->addOption('get', null, InputOption::VALUE_NONE, 'Get an HMAC Key\'s metadata')
    ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete an HMAC Key')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('projectId');
        $accessId = $input->getArgument('accessId');
        if ($input->getOption('activate')) {
            activate_hmac_key($accessId, $projectId);
        } elseif ($input->getOption('deactivate')) {
            deactivate_hmac_key($accessId, $projectId);
        } elseif ($input->getOption('get')) {
            get_hmac_key($accessId, $projectId);
        } elseif ($input->getOption('delete')) {
            delete_hmac_key($accessId, $projectId);
        } else {
            throw new \Exception(
            'You must provide --activate, --deactivate, --get, or --delete with an HMAC key accessId.'
          );
        }
    });

$application->add(new Command('enable-default-kms-key'))
    ->setDescription('Enable default KMS encryption for a bucket.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command enables default KMS encryption for bucket.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('project', InputArgument::REQUIRED, 'Your billable Google Cloud Project ID')
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addArgument('kms-key-name', InputArgument::REQUIRED, 'KMS key ID to use as the default KMS key.')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $bucketName = $input->getArgument('bucket');
        $kmsKeyName = $input->getArgument('kms-key-name');
        enable_default_kms_key($projectId, $bucketName, $kmsKeyName);
    });

$application->add(new Command('upload-with-kms-key'))
    ->setDescription('Upload a file using KMS encryption.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command uploads a file using KMS encryption.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('project', InputArgument::REQUIRED, 'Your billable Google Cloud Project ID')
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addArgument('object', InputArgument::REQUIRED, 'The Cloud Storage object name')
    ->addArgument('upload-from', InputArgument::REQUIRED, 'Path to the file to upload')
    ->addArgument('kms-key-name', InputArgument::REQUIRED, 'KMS key ID used to encrypt objects server side.')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $bucketName = $input->getArgument('bucket');
        $objectName = $input->getArgument('object');
        $uploadFrom = $input->getArgument('upload-from');
        $kmsKeyName = $input->getArgument('kms-key-name');
        upload_with_kms_key($projectId, $bucketName, $objectName, $uploadFrom, $kmsKeyName);
    });

$application->add(new Command('get-object-v2-signed-url'))
    ->setDescription('Generate a v2 signed URL for downloading an object.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command generates a v2 signed URL for downloading an object.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addArgument('object', InputArgument::REQUIRED, 'The Cloud Storage object name')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        $objectName = $input->getArgument('object');
        get_object_v2_signed_url($bucketName, $objectName);
    });

$application->add(new Command('get-object-v4-signed-url'))
    ->setDescription('Generate a v4 signed URL for downloading an object.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command generates a v4 signed URL for downloading an object.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addArgument('object', InputArgument::REQUIRED, 'The Cloud Storage object name')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        $objectName = $input->getArgument('object');
        get_object_v4_signed_url($bucketName, $objectName);
    });

$application->add(new Command('get-object-v4-upload-signed-url'))
    ->setDescription('Generate a v4 signed URL for uploading an object.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command generates a v4 signed URL for uploading an object.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addArgument('object', InputArgument::REQUIRED, 'The Cloud Storage object name')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        $objectName = $input->getArgument('object');
        upload_object_v4_signed_url($bucketName, $objectName);
    });

$application->add(new Command('generate-v4-post-policy'))
    ->setDescription('Generate a v4 post policy form for uploading an object.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command generates a v4 post policy form for uploading an object.

<info>php %command.full_name% --help</info>

EOF
    )
    ->addArgument('bucket', InputArgument::REQUIRED, 'The Cloud Storage bucket name')
    ->addArgument('object', InputArgument::REQUIRED, 'The Cloud Storage object name')
    ->setCode(function ($input, $output) {
        $bucketName = $input->getArgument('bucket');
        $objectName = $input->getArgument('object');
        generate_v4_post_policy($bucketName, $objectName);
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
