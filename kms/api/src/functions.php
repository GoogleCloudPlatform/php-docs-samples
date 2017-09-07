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
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $member Must be in the format "user:$userEmail" or
 *        "serviceAccount:$serviceAccountEmail"
 * @param string $role Must be in the format "roles/$role",
 *        "organizations/$organizationId/roles/$role", or "projects/$projectId/roles/$role"
 * @param string $locationId [optional]
 * @return null
 */
function add_member_to_cryptokey_policy($projectId, $keyRingId, $cryptoKeyId, $member, $role, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId
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

    printf('Member %s added to policy for cryptoKey %s in keyRing %s' . PHP_EOL, $member, $cryptoKeyId, $keyRingId);
}
# [END kms_add_member_to_cryptokey_policy]

# [START kms_add_member_to_keyring_policy]
/**
 * Add a member to a KeyRing IAM policy.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $member Must be in the format "user:$userEmail" or
 *        "serviceAccount:$serviceAccountEmail"
 * @param string $role Must be in the format "roles/$role",
 *        "organizations/$organizationId/roles/$role", or "projects/$projectId/roles/$role"
 * @param string $locationId [optional]
 * @return null
 */
function add_member_to_keyring_policy($projectId, $keyRingId, $member, $role, $locationId = 'global')
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
        $locationId,
        $keyRingId
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

    printf('Member %s added to policy for keyRing %s' . PHP_EOL, $member, $keyRingId);
}
# [END kms_add_member_to_keyring_policy]

# [START kms_create_cryptokey]
/**
 * Create a CryptoKey.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $locationId [optional]
 * @return Google_Service_CloudKMS_CryptoKey
 */
function create_cryptokey($projectId, $keyRingId, $cryptoKeyId, $locationId = 'global')
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
        $locationId,
        $keyRingId
    );

    $cryptoKey = new Google_Service_CloudKMS_CryptoKey();
    $cryptoKey->setPurpose($purpose);

    // Create the CryptoKey for your project.
    $newKey = $kms->projects_locations_keyRings_cryptoKeys->create(
        $parent,
        $cryptoKey,
        ['cryptoKeyId' => $cryptoKeyId]
    );

    printf('Created cryptoKey %s in keyRing %s' . PHP_EOL, $cryptoKeyId, $keyRingId);
}
# [END kms_create_cryptokey]

# [START kms_create_cryptokey_version]
/**
 * Create a KeyRing version.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $locationId [optional]
 * @return null
 */
function create_cryptokey_version($projectId, $keyRingId, $cryptoKeyId, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId
    );

    // Create the CryptoKey version for your project.
    $cryptoKeyVersion = new Google_Service_CloudKMS_CryptoKeyVersion();
    $newVersion = $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions
        ->create($parent, $cryptoKeyVersion);

    $number = substr($newVersion->name, strrpos($newVersion->name, '/') + 1);
    printf('Created version %s for cryptoKey %s in keyRing %s' . PHP_EOL, $number, $cryptoKeyId, $keyRingId);
}
# [END kms_create_cryptokey_version]

# [START kms_create_keyring]
/**
 * Create a KeyRing.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $locationId [optional]
 * @return null
 */
function create_keyring($projectId, $keyRingId, $locationId = 'global')
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
        $locationId
    );

    // Create the KeyRing for your project.
    $keyRing = new Google_Service_CloudKMS_KeyRing();
    $kms->projects_locations_keyRings->create(
        $parent,
        $keyRing,
        ['keyRingId' => $keyRingId]
    );

    printf('Created keyRing %s' . PHP_EOL, $keyRingId);
}
# [END kms_create_keyring]

# [START kms_get_keyring]
/**
 * Get a KeyRing.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $locationId [optional]
 * @return null
 */
function get_keyring($projectId, $keyRingId, $locationId = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s',
        $projectId,
        $locationId,
        $keyRingId
    );

    // Get the KeyRing and print it.
    $keyRing = $kms->projects_locations_keyRings->get($parent);
    printf("Name: %s\nCreate Time: %s\n",
        $keyRing->getName(),
        $keyRing->getCreateTime()
    );
}
# [END kms_get_keyring]

# [START kms_list_keyrings]
/**
 * List the KeyRings for a project and location.
 *
 * @param string $projectId
 * @param string $locationId [optional]
 * @return null
 */
function list_keyrings($projectId, $locationId = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s',
        $projectId,
        $locationId
    );

    // Get the CryptoKey versions and print them.
    $keyRings = $kms->projects_locations_keyRings
        ->listProjectsLocationsKeyRings($parent);
    foreach ($keyRings as $keyRing) {
        printf("Name: %s\nCreate Time: %s\n",
            $keyRing->getName(),
            $keyRing->getCreateTime()
        );
    }
}
# [END kms_list_keyrings]

# [START kms_get_cryptokey]
/**
 * Get a CryptoKey.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $locationId [optional]
 * @return null
 */
function get_cryptokey($projectId, $keyRingId, $cryptoKeyId, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId
    );

    // Get the CryptoKey and print it.
    $cryptoKey = $kms->projects_locations_keyRings_cryptoKeys
        ->get($parent);
    printf("Name: %s\nCreate Time: %s\nPurpose: %s\nPrimary Version: %s\n",
        $cryptoKey->getName(),
        $cryptoKey->getCreateTime(),
        $cryptoKey->getPurpose(),
        $cryptoKey->getPrimary()->getName()
    );
}
# [END kms_get_cryptokey]

# [START kms_list_cryptokeys]
/**
 * List the CryptoKeys for a KeyRing.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $locationId [optional]
 * @return null
 */
function list_cryptokeys($projectId, $keyRingId, $locationId = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the CryptoKey.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s',
        $projectId,
        $locationId,
        $keyRingId
    );

    // Get the CryptoKey versions and print them.
    $cryptoKeys = $kms->projects_locations_keyRings_cryptoKeys
        ->listProjectsLocationsKeyRingsCryptoKeys($parent);
    foreach ($cryptoKeys as $cryptoKey) {
        printf("Name: %s\nCreate Time: %s\nPurpose: %s\nPrimary Version: %s\n\n",
            $cryptoKey->getName(),
            $cryptoKey->getCreateTime(),
            $cryptoKey->getPurpose(),
            $cryptoKey->getPrimary()->getName()
        );
    }
}
# [END kms_list_cryptokey_versions]

# [START kms_get_cryptokey_version]
/**
 * Get the version for a CryptoKey.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param int $version
 * @param string $locationId [optional]
 * @return null
 */
function get_cryptokey_version($projectId, $keyRingId, $cryptoKeyId, $version, $locationId = 'global')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud KMS client.
    $kms = new Google_Service_CloudKMS($client);

    // The resource name of the cryptokey version.
    $parent = sprintf('projects/%s/locations/%s/keyRings/%s/cryptoKeys/%s/cryptoKeyVersions/%s',
        $projectId,
        $locationId,
        $keyRingId,
        $cryptoKeyId,
        $version
    );

    // Get the CryptoKey version and print it.
    $cryptoKeyVersion = $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions
        ->get($parent);
    printf("Name: %s\nCreate Time: %s\nState: %s\n",
        $cryptoKeyVersion->getName(),
        $cryptoKeyVersion->getCreateTime(),
        $cryptoKeyVersion->getState()
    );
}
# [END kms_get_cryptokey_version]

# [START kms_list_cryptokey_versions]
/**
 * List the versions for a CryptoKey.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $locationId [optional]
 * @return null
 */
function list_cryptokey_versions($projectId, $keyRingId, $cryptoKeyId, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId
    );

    // Get the CryptoKey versions and print them.
    $versions = $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions
        ->listProjectsLocationsKeyRingsCryptoKeysCryptoKeyVersions($parent);
    foreach ($versions as $cryptoKeyVersion) {
        printf("Name: %s\nCreate Time: %s\nState: %s\n\n",
            $cryptoKeyVersion->getName(),
            $cryptoKeyVersion->getCreateTime(),
            $cryptoKeyVersion->getState()
        );
    }
}
# [END kms_list_cryptokey_versions]

# [START kms_encrypt]
/**
 * Encrypt a text file.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $plaintextFileName The path to the file containing plaintext to encrypt.
 * @param string $ciphertextFileName The path to write the ciphertext.
 * @param string $locationId [optional]
 * @return null
 */
function encrypt($projectId, $keyRingId, $cryptoKeyId, $plaintextFileName, $ciphertextFileName, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId
    );

    // Use the KMS API to encrypt the text.
    $encoded = base64_encode(file_get_contents($plaintextFileName));
    $request = new Google_Service_CloudKMS_EncryptRequest();
    $request->setPlaintext($encoded);
    $response = $kms->projects_locations_keyRings_cryptoKeys->encrypt(
        $name,
        $request
    );

    // Write the encrypted text to a file.
    file_put_contents($ciphertextFileName, base64_decode($response['ciphertext']));
    printf('Saved encrypted text to %s' . PHP_EOL, $ciphertextFileName);
}
# [END kms_encrypt]

# [START kms_decrypt]
/**
 * Decrypt a text file.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $ciphertextFileName The path to the ciphertext file to decrypt.
 * @param string $plaintextFileName The path to write the decrypted plaintext file.
 * @param string $locationId [optional]
 * @return null
 */
function decrypt($projectId, $keyRingId, $cryptoKeyId, $ciphertextFileName, $plaintextFileName, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId
    );

    // Use the KMS API to decrypt the text.
    $ciphertext = base64_encode(file_get_contents($ciphertextFileName));
    $request = new Google_Service_CloudKMS_DecryptRequest();
    $request->setCiphertext($ciphertext);
    $response = $kms->projects_locations_keyRings_cryptoKeys->decrypt(
        $name,
        $request
    );

    // Write the decrypted text to a file.
    file_put_contents($plaintextFileName, base64_decode($response['plaintext']));
    printf('Saved decrypted text to %s' . PHP_EOL, $plaintextFileName);
}
# [END kms_decrypt]

# [START kms_destroy_cryptokey_version]
/**
 * Destroy a CryptoKey version.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $version
 * @param string $locationId [optional]
 * @return Google_Service_CloudKMS_CryptoKeyVersion
 */
function destroy_cryptokey_version($projectId, $keyRingId, $cryptoKeyId, $version, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId,
        $version
    );

    // Destroy the CryptoKey version.
    $request = new Google_Service_CloudKMS_DestroyCryptoKeyVersionRequest();
    $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions->destroy(
        $parent,
        $request
    );

    printf('Destroyed version %s for cryptoKey %s in keyRing %s' . PHP_EOL, $version, $cryptoKeyId, $keyRingId);
}
# [END kms_destroy_cryptokey_version]

# [START kms_restore_cryptokey_version]
/**
 * Restore a CryptoKey version.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $version
 * @param string $locationId [optional]
 * @return Google_Service_CloudKMS_CryptoKeyVersion
 */
function restore_cryptokey_version($projectId, $keyRingId, $cryptoKeyId, $version, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId,
        $version
    );

    // Restore the CryptoKey version.
    $request = new Google_Service_CloudKMS_RestoreCryptoKeyVersionRequest();
    $kms->projects_locations_keyRings_cryptoKeys_cryptoKeyVersions->restore(
        $parent,
        $request
    );

    printf('Restored version %s for cryptoKey %s in keyRing %s' . PHP_EOL, $version, $cryptoKeyId, $keyRingId);
}
# [END kms_restore_cryptokey_version]

# [START kms_disable_cryptokey_version]
/**
 * Disable a CryptoKey version.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param int $version
 * @param string $locationId [optional]
 * @return null
 */
function disable_cryptokey_version($projectId, $keyRingId, $cryptoKeyId, $version, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId,
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

    printf('Disabled version %s for cryptoKey %s in keyRing %s' . PHP_EOL, $version, $cryptoKeyId, $keyRingId);
}
# [END kms_disable_cryptokey_version]

# [START kms_enable_cryptokey_version]
/**
 * Enable a CryptoKey version.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param int $version
 * @param string $locationId [optional]
 * @return null
 */
function enable_cryptokey_version($projectId, $keyRingId, $cryptoKeyId, $version, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId,
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

    printf('Enabled version %s for cryptoKey %s in keyRing %s' . PHP_EOL, $version, $cryptoKeyId, $keyRingId);
}
# [END kms_enable_cryptokey_version]

# [START kms_get_cryptokey_policy]
/**
 * Get the IAM policy for a CryptoKey.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $locationId [optional]
 * @return null
 */
function get_cryptokey_policy($projectId, $keyRingId, $cryptoKeyId, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId
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
 * @param string $keyRingId
 * @param string $locationId [optional]
 * @return null
 */
function get_keyring_policy($projectId, $keyRingId, $locationId = 'global')
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
        $locationId,
        $keyRingId
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
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param string $member Must be in the format "user:$userEmail" or
 *        "serviceAccount:$serviceAccountEmail"
 * @param string $role Must be in the format "roles/$role",
 *        "organizations/$organizationId/roles/$role", or "projects/$projectId/roles/$role"
 * @param string $locationId [optional]
 * @return null
 */
function remove_member_from_cryptokey_policy($projectId, $keyRingId, $cryptoKeyId, $member, $role, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId
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

    printf('Member %s removed from policy for cryptoKey %s in keyRing %s' . PHP_EOL,
        $member,
        $cryptoKeyId,
        $keyRingId);
}
# [END kms_remove_member_from_cryptokey_policy]

# [START kms_remove_member_from_keyring_policy]
/**
 * Remove a member from a KeyRing IAM policy.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $member Must be in the format "user:$userEmail" or
 *        "serviceAccount:$serviceAccountEmail"
 * @param string $role Must be in the format "roles/$role",
 *        "organizations/$organizationId/roles/$role", or "projects/$projectId/roles/$role"
 * @param string $locationId [optional]
 * @return null
 */
function remove_member_from_keyring_policy($projectId, $keyRingId, $member, $role, $locationId = 'global')
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
        $locationId,
        $keyRingId
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

    printf('Member %s removed from policy for keyRing %s' . PHP_EOL,
        $member,
        $keyRingId);
}
# [END kms_remove_member_from_keyring_policy]

# [START kms_set_cryptokey_primary_version]
/**
 * Set a CryptoKey version as primary.
 *
 * @param string $projectId
 * @param string $keyRingId
 * @param string $cryptoKeyId
 * @param int $version
 * @param string $locationId [optional]
 * @return null
 */
function set_cryptokey_primary_version($projectId, $keyRingId, $cryptoKeyId, $version, $locationId = 'global')
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
        $locationId,
        $keyRingId,
        $cryptoKeyId
    );

    // Update the CryptoKey primary version.
    $request = new Google_Service_CloudKMS_UpdateCryptoKeyPrimaryVersionRequest();
    $request->setCryptoKeyVersionId($version);
    $cryptoKey = $kms->projects_locations_keyRings_cryptoKeys->updatePrimaryVersion(
        $parent,
        $request
    );

    printf('Set %s as primary version for cryptoKey %s in keyRing %s' . PHP_EOL, $version, $cryptoKeyId, $keyRingId);
}
# [END kms_set_cryptokey_primary_version]
