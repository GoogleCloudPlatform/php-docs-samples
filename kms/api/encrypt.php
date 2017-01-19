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

require_once __DIR__ . '/vendor/autoload.php';

if (count($argv) < 4) {
    die('usage: encrypt.php [key_name] [infile] [outfile]');
}

list($keyName, $infile, $outfile) = array_slice($argv, 1);

# [START encrypt]
$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes([
    'https://www.googleapis.com/auth/cloud-platform'
]);

$kms = new Google_Service_CloudKMS($client);

// This client library requires we base64 encode binary data.
$encoded = base64_encode(file_get_contents($infile));

$request = new Google_Service_CloudKMS_EncryptRequest();
$request->setPlaintext($encoded);
$response = $kms->projects_locations_keyRings_cryptoKeys->encrypt(
    $keyName,
    $request
);

$ciphertext = $response['ciphertext'];
# [END encrypt]

file_put_contents($outfile, $ciphertext);
