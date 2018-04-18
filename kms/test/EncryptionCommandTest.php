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
use Google_Service_CloudKMS_DecryptRequest;
use Symfony\Component\Console\Tester\CommandTester;

class EncryptionCommandTest extends \PHPUnit_Framework_TestCase
{
    private static $encryptedFile;
    private $commandTester;
    private $projectId;
    private $ring;
    private $key;

    public function setUp()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            return $this->markTestSkipped('Set the GOOGLE_PROJECT_ID environment variable');
        }
        if (!$ring = getenv('GOOGLE_KMS_KEYRING')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_KEYRING environment variable');
        }
        if (!$key = getenv('GOOGLE_KMS_CRYPTOKEY')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_CRYPTOKEY environment variable');
        }

        $this->projectId = $projectId;
        $this->ring = $ring;
        $this->key = $key;

        $application = require __DIR__ . '/../kms.php';
        $this->commandTester = new CommandTester($application->get('encryption'));
    }

    public function testEncrypt()
    {
        $infile = __DIR__ . '/data/plaintext.txt';
        $outfile = sys_get_temp_dir() . '/plaintext.txt.encrypted';

        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                'infile' => $infile,
                'outfile' => $outfile,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->assertTrue(file_exists($outfile));

        // assert the text matches
        $parent = sprintf(
            'projects/%s/locations/global/keyRings/%s/cryptoKeys/%s',
            $this->projectId,
            $this->ring,
            $this->key
        );
        // Instantiate the client, authenticate, and add scopes.
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope('https://www.googleapis.com/auth/cloud-platform');
        $kms = new Google_Service_CloudKMS($client);
        // create the decrypt request
        $request = new Google_Service_CloudKMS_DecryptRequest([
            'ciphertext' => base64_encode(file_get_contents($outfile))
        ]);
        $response = $kms->projects_locations_keyRings_cryptoKeys->decrypt(
            $parent,
            $request
        );
        $this->assertEquals(
            file_get_contents(__DIR__ . '/data/plaintext.txt'),
            base64_decode($response['plaintext'])
        );

        $this->expectOutputString(sprintf('Saved encrypted text to %s' . PHP_EOL, $outfile));

        self::$encryptedFile = $outfile;
    }

    /** @depends testEncrypt */
    public function testDecrypt()
    {
        $outfile = sys_get_temp_dir() . '/plaintext.txt.decrypted';

        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                'infile' => self::$encryptedFile,
                'outfile' => $outfile,
                '--decrypt' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );
        $this->assertTrue(file_exists($outfile));
        $this->assertEquals(
            file_get_contents(__DIR__ . '/data/plaintext.txt'),
            file_get_contents($outfile)
        );

        $this->expectOutputString(sprintf('Saved decrypted text to %s' . PHP_EOL, $outfile));
    }
}
