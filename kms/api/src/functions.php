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

use Google_Client;
use Google_Service_CloudKMS;
use Google_Service_CloudKMS_Binding;
use Google_Service_CloudKMS_CryptoKey;
use Google_Service_CloudKMS_CryptoKeyVersion;
use Google_Service_CloudKMS_DecryptRequest;
use Google_Service_CloudKMS_DestroyCryptoKeyVersionRequest;
use Google_Service_CloudKMS_EncryptRequest;
use Google_Service_CloudKMS_KeyRing;
use Google_Service_CloudKMS_RestoreCryptoKeyVersionRequest;
use Google_Service_CloudKMS_SetIamPolicyRequest;
use Google_Service_CloudKMS_UpdateCryptoKeyPrimaryVersionRequest;

# [START kms_add_member_to_cryptokey_policy]
/**
 * Add a member to a CryptoKey IAM policy.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param string $member Must be in the format "user:$userEmail" or
 *        "serviceAccount:$serviceAccountEmail"
 * @param string $role Must be in the format "roles/$role",
 *        "organizations/$organizationId/roles/$role", or "projects/$projectId/roles/$role"
 * @param string $location [optional]
 * @return null
 */
function add_member_to_cryptokey_policy($projectId, $ring, $key, $member, $role, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s',
        $projectId,
        $location,
        $ring,
        $key
    );

    // Get the current IAM policy and add the new account to it.
    $policy = $kms->projects_locations_keyRings_cryptoKeys->getIamPolicy($parent);
    $bindings = $policy->getBindings();
    $bindings[] = new Google_Service_CloudKMS_Binding([
        'members' => [$member],
        'role' => $role,
    ]);
    $policy->setBindings($bindings);

    // Set the new IAM Policy.
    $request = new Google_Service_CloudKMS_SetIamPolicyRequest(['policy' => $policy]);
    $kms->projects_locations_keyRings_cryptoKeys->setIamPolicy(
        $parent,
        $request
    );

    printf('Member %s added to policy for key %s in keyring %s' . PHP_EOL, $member, $key, $ring);
}
# [END kms_add_member_to_cryptokey_policy]

# [START kms_add_member_to_keyring_policy]
/**
 * Add a member to a KeyRing IAM policy.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $member Must be in the format "user:$userEmail" or
 *        "serviceAccount:$serviceAccountEmail"
 * @param string $role Must be in the format "roles/$role",
 *        "organizations/$organizationId/roles/$role", or "projects/$projectId/roles/$role"
 * @param string $location [optional]
 * @return null
 */
function add_member_to_keyring_policy($projectId, $ring, $member, $role, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the KeyRing.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s',
        $projectId,
        $location,
        $ring
    );

    // Get the current IAM policy and add the new account to it.
    $policy = $kms->projects_locations_keyRings->getIamPolicy($parent);
    $bindings = $policy->getBindings();
    $bindings[] = new Google_Service_CloudKMS_Binding([
        'members' => [$member],
        'role' => $role,
    ]);
    $policy->setBindings($bindings);

    // Set the new IAM Policy.
    $request = new Google_Service_CloudKMS_SetIamPolicyRequest(['policy' => $policy]);
    $kms->projects_locations_keyRings->setIamPolicy(
        $parent,
        $request
    );

    printf('Member %s added to policy for keyring %s' . PHP_EOL, $member, $ring);
}
# [END kms_add_member_to_keyring_policy]

# [START kms_create_cryptokey]
/**
 * Create a CryptoKey.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param string $location [optional]
 * @return Google_Service_CloudKMS_CryptoKey
 */
function create_cryptokey($projectId, $ring, $key, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // This will allow the API access to the key for encryption and decryption.
    $purpose = 'ENCRYPT_DECRYPT';

    // The resource name of the KeyRing associated with the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s',
        $projectId,
        $location,
        $ring
    );

    $cryptoKey = new Google_Service_CloudKMS_CryptoKey();
    $cryptoKey->setPurpose($purpose);

    // Create the CryptoKey for your project.
    $newKey = $kms->projects_locations_keyRings_cryptoKeys->create(
        $parent,
        $cryptoKey,
        ['cryptoKeyId' => $key]
    );

    printf('Created key %s in keyring %s' . PHP_EOL, $key, $ring);
}
# [END kms_create_cryptokey]

# [START kms_create_cryptokey_version]
/**
 * Create a KeyRing version.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param string $location [optional]
 * @return null
 */
function create_cryptokey_version($projectId, $ring, $key, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // This will allow the API access to the key for encryption and decryption.
    $purpose = 'ENCRYPT_DECRYPT';

    // The resource name of the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s',
        $projectId,
        $location,
        $ring,
        $key
    );

    // Create the CryptoKey version for your project.
    $cryptoKeyVersion = new Google_Service_CloudKMS_CryptoKeyVersion();
    $newVersion = $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions
        ->create($parent, $cryptoKeyVersion);

    $number = substr($newVersion->name, strrpos($newVersion->name, '/') + 1);
    printf('Created version %s for key %s in keyring %s' . PHP_EOL, $number, $key, $ring);
}
# [END kms_create_cryptokey_version]

# [START kms_create_keyring]
/**
 * Create a KeyRing.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $location [optional]
 * @return null
 */
function create_keyring($projectId, $ring, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the location associated with the KeyRing.
    $parent = sprintf('projects/%s/locations/%s',
        $projectId,
        $location
    );

    // Create the KeyRing for your project.
    $keyRing = new Google_Service_CloudKMS_KeyRing();
    $kms->projects_locations_keyRings->create(
        $parent,
        $keyRing,
        ['keyRingId' => $ring]
    );

    printf('Created keyring %s' . PHP_EOL, $ring);
}
# [END kms_create_keyring]

# [START kms_encrypt]
/**
 * Encrypt a text file.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param string $infile The path to a text file.
 * @param string $outfile The path to write the encrypted file.
 * @param string $location [optional]
 * @return null
 */
function encrypt($projectId, $ring, $key, $infile, $outfile, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the cryptokey.
    $name = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s',
        $projectId,
        $location,
        $ring,
        $key
    );

    // Use the KMS API to encrypt the text.
    $encoded = base64_encode(file_get_contents($infile));
    $request = new Google_Service_CloudKMS_EncryptRequest();
    $request->setPlaintext($encoded);
    $response = $kms->projects_locations_keyRings_cryptoKeys->encrypt(
        $name,
        $request
    );

    // Write the encrypted text to a file.
    file_put_contents($outfile, $response['ciphertext']);
    printf('Saved encrypted text to %s' . PHP_EOL, $outfile);
}
# [END kms_encrypt]

# [START kms_decrypt]
/**
 * Decrypt a text file.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param string $infile The path to an encrypted file.
 * @param string $outfile The path to write the decrypted file.
 * @param string $location [optional]
 * @return null
 */
function decrypt($projectId, $ring, $key, $infile, $outfile, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the cryptokey.
    $name = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s',
        $projectId,
        $location,
        $ring,
        $key
    );

    // Use the KMS API to decrypt the text.
    $ciphertext = file_get_contents($infile);
    $request = new Google_Service_CloudKMS_DecryptRequest();
    $request->setCiphertext($ciphertext);
    $response = $kms->projects_locations_keyRings_cryptoKeys->decrypt(
        $name,
        $request
    );

    // Write the decrypted text to a file.
    $plaintext = base64_decode($response['plaintext']);
    file_put_contents($outfile, $plaintext);
    printf('Saved decrypted text to %s' . PHP_EOL, $outfile);
}
# [END kms_decrypt]

# [START kms_destroy_cryptokey_version]
/**
 * Destroy a CryptoKey version.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param string $version
 * @param string $location [optional]
 * @return Google_Service_CloudKMS_CryptoKeyVersion
 */
function destroy_cryptokey_version($projectId, $ring, $key, $version, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the CryptoKey version.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s/cryptoKeyVersions/%s',
        $projectId,
        $location,
        $ring,
        $key,
        $version
    );

    // Destroy the CryptoKey version.
    $request = new Google_Service_CloudKMS_DestroyCryptoKeyVersionRequest();
    $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions->destroy(
        $parent,
        $request
    );

    printf('Destroyed version %s for key %s in keyring %s' . PHP_EOL, $version, $key, $ring);
}
# [END kms_destroy_cryptokey_version]

# [START kms_restore_cryptokey_version]
/**
 * Restore a CryptoKey version.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param string $version
 * @param string $location [optional]
 * @return Google_Service_CloudKMS_CryptoKeyVersion
 */
function restore_cryptokey_version($projectId, $ring, $key, $version, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the CryptoKey version.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s/cryptoKeyVersions/%s',
        $projectId,
        $location,
        $ring,
        $key,
        $version
    );

    // Restore the CryptoKey version.
    $request = new Google_Service_CloudKMS_RestoreCryptoKeyVersionRequest();
    $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions->restore(
        $parent,
        $request
    );

    printf('Restored version %s for key %s in keyring %s' . PHP_EOL, $version, $key, $ring);
}
# [END kms_restore_cryptokey_version]

# [START kms_disable_cryptokey_version]
/**
 * Disable a CryptoKey version.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param int $version
 * @param string $location [optional]
 * @return null
 */
function disable_cryptokey_version($projectId, $ring, $key, $version, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the KeyRing associated with the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s/cryptoKeyVersions/%s',
        $projectId,
        $location,
        $ring,
        $key,
        $version
    );

    // Disable the CryptoKey version.
    $cryptoKeyVersion = $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions
        ->get($parent);
    $cryptoKeyVersion->setState('DISABLED');

    $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions->patch(
        $parent,
        $cryptoKeyVersion,
        ['updateMask' => 'state']
    );

    printf('Disabled version %s for key %s in keyring %s' . PHP_EOL, $version, $key, $ring);
}
# [END kms_disable_cryptokey_version]

# [START kms_enable_cryptokey_version]
/**
 * Enable a CryptoKey version.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param int $version
 * @param string $location [optional]
 * @return null
 */
function enable_cryptokey_version($projectId, $ring, $key, $version, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the KeyRing associated with the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s/cryptoKeyVersions/%s',
        $projectId,
        $location,
        $ring,
        $key,
        $version
    );

    // Enable the CryptoKey version.
    $cryptoKeyVersion = $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions
        ->get($parent);
    $cryptoKeyVersion->setState('ENABLED');

    $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions->patch(
        $parent,
        $cryptoKeyVersion,
        ['updateMask' => 'state']
    );

    printf('Enabled version %s for key %s in keyring %s' . PHP_EOL, $version, $key, $ring);
}
# [END kms_enable_cryptokey_version]

# [START kms_get_cryptokey_policy]
/**
 * Get the IAM policy for a CryptoKey.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param string $location [optional]
 * @return null
 */
function get_cryptokey_policy($projectId, $ring, $key, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s',
        $projectId,
        $location,
        $ring,
        $key
    );

    // Get the current IAM policy and print it.
    $policy = $kms->projects_locations_keyRings_cryptoKeys->getIamPolicy($parent);
    foreach ($policy->getBindings() as $binding) {
        printf("Role: %s\nMembers:\n%s\n",
            $binding->getRole(),
            implode("\n", $binding->getMembers())
        );
    }
}
# [END kms_get_cryptokey_policy]

# [START kms_get_keyring_policy]
/**
 * Get the IAM policy for a KeyRing.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $location [optional]
 * @return null
 */
function get_keyring_policy($projectId, $ring, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the location associated with the key rings.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s',
        $projectId,
        $location,
        $ring
    );

    // Get the current IAM policy and print it.
    $policy = $kms->projects_locations_keyRings->getIamPolicy($parent);
    foreach ($policy->getBindings() as $binding) {
        printf("Role: %s\nMembers:\n%s\n",
            $binding->getRole(),
            implode("\n", $binding->getMembers())
        );
    }
}
# [END kms_get_keyring_policy]

# [START kms_remove_member_from_cryptokey_policy]
/**
 * Remove a member from a CryptoKey IAM policy.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param string $member Must be in the format "user:$userEmail" or
 *        "serviceAccount:$serviceAccountEmail"
 * @param string $role Must be in the format "roles/$role",
 *        "organizations/$organizationId/roles/$role", or "projects/$projectId/roles/$role"
 * @param string $location [optional]
 * @return null
 */
function remove_member_from_cryptokey_policy($projectId, $ring, $key, $member, $role, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the KeyRing associated with the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s',
        $projectId,
        $location,
        $ring,
        $key
    );

    // Get the current IAM policy and remove the member from it.
    $policy = $kms->projects_locations_keyRings_cryptoKeys->getIamPolicy($parent);
    foreach ($policy->getBindings() as $binding) {
        if ($binding->getRole() == $role) {
            $members = $binding->getMembers();
            if (false !== $i = array_search($member, $members)) {
                unset($members[$i]);
                $binding->setMembers($members);
                break;
            }
        }
    }

    // Set the new IAM Policy.
    $request = new Google_Service_CloudKMS_SetIamPolicyRequest(['policy' => $policy]);
    $kms->projects_locations_keyRings_cryptoKeys->setIamPolicy(
        $parent,
        $request
    );

    printf('Member %s removed from policy for key %s in keyring %s' . PHP_EOL,
        $member,
        $key,
        $ring);
}
# [END kms_remove_member_from_cryptokey_policy]

# [START kms_remove_member_from_keyring_policy]
/**
 * Remove a member from a KeyRing IAM policy.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $member Must be in the format "user:$userEmail" or
 *        "serviceAccount:$serviceAccountEmail"
 * @param string $role Must be in the format "roles/$role",
 *        "organizations/$organizationId/roles/$role", or "projects/$projectId/roles/$role"
 * @param string $location [optional]
 * @return null
 */
function remove_member_from_keyring_policy($projectId, $ring, $member, $role, $location = 'global')
{
    // Instantiate the client, authenticate using Application Default Credentials,
    // and add the scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the location associated with the KeyRing.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s',
        $projectId,
        $location,
        $ring
    );

    // Get the current IAM policy and remove the member from it.
    $policy = $kms->projects_locations_keyRings->getIamPolicy($parent);
    foreach ($policy->getBindings() as $binding) {
        if ($binding->getRole() == $role) {
            $members = $binding->getMembers();
            if (false !== $i = array_search($member, $members)) {
                unset($members[$i]);
                $binding->setMembers($members);
                break;
            }
        }
    }

    // Set the new IAM Policy.
    $request = new Google_Service_CloudKMS_SetIamPolicyRequest(['policy' => $policy]);
    $kms->projects_locations_keyRings->setIamPolicy(
        $parent,
        $request
    );

    printf('Member %s removed from policy for keyring %s' . PHP_EOL,
        $member,
        $ring);
}
# [END kms_remove_member_from_keyring_policy]

# [START kms_set_cryptokey_primary_version]
/**
 * Set a CryptoKey version as primary.
 *
 * @param string $projectId
 * @param string $ring
 * @param string $key
 * @param int $version
 * @param string $location [optional]
 * @return null
 */
function set_cryptokey_primary_version($projectId, $ring, $key, $version, $location = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the KeyRing associated with the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s',
        $projectId,
        $location,
        $ring,
        $key
    );

    // Update the CryptoKey primary version.
    $request = new Google_Service_CloudKMS_UpdateCryptoKeyPrimaryVersionRequest();
    $request->setCryptoKeyVersionId($version);
    $cryptoKey = $kms->projects_locations_keyRings_cryptoKeys->updatePrimaryVersion(
        $parent,
        $request
    );

    printf('Set %s as primary version for key %s in keyring %s' . PHP_EOL, $version, $key, $ring);
}
# [END kms_set_cryptokey_primary_version]
