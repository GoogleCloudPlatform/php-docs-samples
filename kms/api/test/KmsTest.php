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

namespace Google\Cloud\Samples\KMS;

use Google_Client;
use Google_Service_CloudKMS;
use Google_Service_CloudKMS_DecryptRequest;
use Symfony\Component\Process\Process;

class KmsTest extends \PHPUnit_Framework_TestCase
{
    private $projectId;
    private $kms;

    public function setUp()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            return $this->markTestSkipped('Set the GOOGLE_PROJECT_ID environment variable');
        }

        $this->projectId = $projectId;
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes([
            'https://www.googleapis.com/auth/cloud-platform'
        ]);
        $this->kms = new Google_Service_CloudKMS($client);
    }

    public function testCreateCryptoKey()
    {
        if (!$keyRing = getenv('GOOGLE_KMS_KEYRING')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_KEYRING environment variable');
        }
        $name = 'test-crypto-key-' . time();
        $process = $this->createProcess('create_crypto_key.php', [
            $this->projectId,
            $keyRing,
            $name
        ]);
        $process->run();

        $this->assertTrue($process->isSuccessful());
        $parent = sprintf(
            'projects/%s/locations/global/keyRings/%s/cryptoKeys/%s',
            $this->projectId,
            $keyRing,
            $name
        );
        $cryptoKey = $this->kms->projects_locations_keyRings_cryptoKeys->get($parent);
        $this->assertNotNull($cryptoKey);
        $this->assertEquals($parent, $cryptoKey->getName());
    }

    public function testCreateKeyRing()
    {
        $name = 'test-key-ring-' . time();
        $process = $this->createProcess('create_key_ring.php', [
            $this->projectId,
            $name
        ]);
        $process->run();

        $this->assertTrue($process->isSuccessful());
        $parent = sprintf(
            'projects/%s/locations/global/keyRings/%s', $this->projectId, $name
        );
        $keyRing = $this->kms->projects_locations_keyRings->get($parent);
        $this->assertNotNull($keyRing);
        $this->assertEquals($parent, $keyRing->getName());
    }

    public function testEncrypt()
    {
        if (!$keyRing = getenv('GOOGLE_KMS_KEYRING')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_KEYRING environment variable');
        }
        if (!$cryptoKey = getenv('GOOGLE_KMS_CRYPTOKEY')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_CRYPTOKEY environment variable');
        }
        $parent = sprintf(
            'projects/%s/locations/global/keyRings/%s/cryptoKeys/%s',
            $this->projectId,
            $keyRing,
            $cryptoKey
        );
        $inFile = __DIR__ . '/data/plaintext.txt';
        $outFile = sys_get_temp_dir() . '/plaintext.txt.encrypted';
        $process = $this->createProcess('encrypt.php', [
            $parent,
            $inFile,
            $outFile
        ]);
        $process->run();

        $this->assertTrue($process->isSuccessful());
        $this->assertTrue(file_exists($outFile));

        // assert the text matches
        $request = new Google_Service_CloudKMS_DecryptRequest();
        $request->setCiphertext(file_get_contents($outFile));
        $response = $this->kms->projects_locations_keyRings_cryptoKeys->decrypt(
            $parent,
            $request
        );
        $this->assertEquals(
            file_get_contents(__DIR__ . '/data/plaintext.txt'),
            base64_decode($response['plaintext'])
        );
    }

    public function testDecrypt()
    {
        if (!$keyRing = getenv('GOOGLE_KMS_KEYRING')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_KEYRING environment variable');
        }
        if (!$cryptoKey = getenv('GOOGLE_KMS_CRYPTOKEY')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_CRYPTOKEY environment variable');
        }
        $parent = sprintf(
            'projects/%s/locations/global/keyRings/%s/cryptoKeys/%s',
            $this->projectId,
            $keyRing,
            $cryptoKey
        );
        $inFile = __DIR__ . '/data/plaintext.txt.encrypted';
        $outFile = sys_get_temp_dir() . '/plaintext.txt.decrypted';
        $process = $this->createProcess('decrypt.php', [
            $parent,
            $inFile,
            $outFile
        ]);
        $process->run();

        $this->assertTrue($process->isSuccessful());
        $this->assertTrue(file_exists($outFile));
        $this->assertEquals(
            file_get_contents(__DIR__ . '/data/plaintext.txt'),
            file_get_contents($outFile)
        );
    }

    private function createProcess($file, $args = [])
    {
        $cmd = sprintf('php %s/../%s %s',
            __DIR__,
            $file,
            implode(' ', $args)
        );
        return new Process($cmd);
    }
}
