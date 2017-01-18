<?php

require_once __DIR__ . '/vendor/autoload.php';

if (count($argv) < 4) {
    die('usage: decrypt.php [key_name] [infile] [outfile]');
}

list($keyName, $infile, $outfile) = array_slice($argv, 1);

# [START decrypt]
$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes([
    'https://www.googleapis.com/auth/cloud-platform'
]);
$kms = new Google_Service_CloudKMS($client);

$ciphertext = file_get_contents($infile);

$request = new Google_Service_CloudKMS_DecryptRequest();
$request->setCiphertext($ciphertext);
$response = $kms->projects_locations_keyRings_cryptoKeys->decrypt(
    $keyName,
    $request
);

// The plaintext response comes back base64 decoded.
$plaintext = base64_decode($response['plaintext'])
# [END decrypt]

file_put_contents($outfile, $plaintext);
