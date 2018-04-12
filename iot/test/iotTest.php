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

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for dlp commands.
 */
class dlpTest extends \PHPUnit_Framework_TestCase
{
    private static $testId;
    private static $registryId;
    private static $devices = [];

    public function checkEnv($var)
    {
        if (!getenv($var)) {
            self::markTestSkipped(sprintf('Set the "%s" environment variable', $var));
        }
    }

    public function setUp()
    {
        $this->checkEnv('GOOGLE_APPLICATION_CREDENTIALS');
        $this->checkEnv('GCLOUD_PROJECT');
    }

    public static function setUpBeforeClass()
    {
        self::$testId = time() . '-' . rand();
    }

    public static function tearDownAfterClass()
    {
        foreach (self::$devices as $deviceId) {
            printf('Cleaning up Device %s' . PHP_EOL, $deviceId);
            self::runCommand('delete-device', [
                'registry' => self::$registryId,
                'device' => $deviceId,
            ]);
        }
        if (self::$registryId) {
            printf('Cleaning up Registry %s' . PHP_EOL, self::$registryId);
            self::runCommand('delete-registry', [
                'registry' => self::$registryId
            ]);
        }
    }

    public function testCreateRegistry()
    {
        $this->checkEnv('GOOGLE_PUBSUB_TOPIC');

        $registryId = 'test-registry-' . self::$testId;

        $output = $this->runCommand('create-registry', [
            'registry' => $registryId,
            'pubsub-topic' => getenv('GOOGLE_PUBSUB_TOPIC'),
        ]);
        self::$registryId = $registryId;
        $this->assertContains('Id: ' . $registryId, $output);
    }

    /** @depends testCreateRegistry */
    public function testListRegistries()
    {
        $output = $this->runCommand('list-registries');
        $this->assertContains(self::$registryId, $output);
    }

    /** @depends testCreateRegistry */
    public function testGetRegistry()
    {
        $output = $this->runCommand('get-registry', [
            'registry' => self::$registryId,
        ]);
        $this->assertContains(self::$registryId, $output);
    }

    /** @depends testCreateRegistry */
    public function testIamPolicy()
    {
        $email = 'betterbrent@google.com';
        $output = $this->runCommand('set-iam-policy', [
            'registry' => self::$registryId,
            'role' => 'roles/viewer',
            'member' => 'user:' . $email
        ]);
        $this->assertContains($email, $output);

        $output = $this->runCommand('get-iam-policy', [
            'registry' => self::$registryId,
        ]);
        $this->assertContains($email, $output);
    }

    /** @depends testCreateRegistry */
    public function testCreateRsaDevice()
    {
        $deviceId = 'test-rsa-device-' . self::$testId;

        $output = $this->runCommand('create-rsa-device', [
            'registry' => self::$registryId,
            'device' => $deviceId,
            'certificate-file' => __DIR__ . '/data/rsa_cert.pem',
        ]);
        self::$devices[] = $deviceId;
        $this->assertContains($deviceId, $output);
    }

    /** @depends testCreateRsaDevice */
    public function testSetDeviceState()
    {
        $this->checkEnv('GOOGLE_IOT_DEVICE_CERTIFICATE_B64');

        $iotCert = base64_decode(getenv('GOOGLE_IOT_DEVICE_CERTIFICATE_B64'));
        $iotCertFile = tempnam(sys_get_temp_dir(), 'iot-cert');
        file_put_contents($iotCertFile, $iotCert);

        $data = '{"data":"example of state data"}';
        $output = $this->runCommand('set-device-state', [
            'registry' => self::$registryId,
            'device' => self::$devices[0],
            'certificate-file' => $iotCertFile,
            'state-data' => $data,
        ]);

        $output = $this->runCommand('get-device-state', [
            'registry' => self::$registryId,
            'device' => self::$devices[0],
        ]);
        $this->assertContains('Data: ' . $data, $output);
    }

    /** @depends testCreateRsaDevice */
    public function testListDevices()
    {
        $output = $this->runCommand('list-devices', [
            'registry' => self::$registryId,
        ]);
        $this->assertContains(self::$devices[0], $output);
    }

    /** @depends testCreateRsaDevice */
    public function testGetDevice()
    {
        $output = $this->runCommand('get-device', [
            'registry' => self::$registryId,
            'device' => self::$devices[0],
        ]);
        $this->assertContains(self::$devices[0], $output);
    }

    /** @depends testCreateRsaDevice */
    public function testSetDeviceConfig()
    {
        $config = '{"data":"example of config data"}';
        $output = $this->runCommand('set-device-config', [
            'registry' => self::$registryId,
            'device' => self::$devices[0],
            'config' => $config,
        ]);
        $this->assertContains('Version: 2', $output);
        $this->assertContains('Data: ' . $config, $output);
    }

    /** @depends testSetDeviceConfig */
    public function testGetDeviceConfigs()
    {
        $output = $this->runCommand('get-device-configs', [
            'registry' => self::$registryId,
            'device' => self::$devices[0],
        ]);
        $this->assertContains('Version: 2', $output);
    }

    /** @depends testCreateRegistry */
    public function testCreateEsDevice()
    {
        $deviceId = 'test-es-device-' . self::$testId;

        $output = $this->runCommand('create-es-device', [
            'registry' => self::$registryId,
            'device' => $deviceId,
            'public-key-file' => __DIR__ . '/data/ec_public.pem',
        ]);
        self::$devices[] = $deviceId;
        $this->assertContains($deviceId, $output);
    }

    /** @depends testCreateRegistry */
    public function testCreateUnauthDevice()
    {
        $deviceId = 'test-unauth-device-' . self::$testId;

        $output = $this->runCommand('create-unauth-device', [
            'registry' => self::$registryId,
            'device' => $deviceId,
        ]);
        self::$devices[] = $deviceId;
        $this->assertContains($deviceId, $output);
    }

    /** @depends testCreateUnauthDevice */
    public function testPatchEs()
    {
        $deviceId = 'test-es-device-to-patch' . self::$testId;

        $this->runCommand('create-unauth-device', [
            'registry' => self::$registryId,
            'device' => $deviceId,
        ]);
        self::$devices[] = $deviceId;

        $output = $this->runCommand('patch-es-device', [
            'registry' => self::$registryId,
            'device' => $deviceId,
            'public-key-file' => __DIR__ . '/data/ec_public.pem',
        ]);

        $this->assertContains('Updated device', $output);
    }

    /** @depends testCreateRegistry */
    public function testPatchRsa()
    {
        $deviceId = 'test-rsa-device-to-patch' . self::$testId;

        $this->runCommand('create-unauth-device', [
            'registry' => self::$registryId,
            'device' => $deviceId,
        ]);
        self::$devices[] = $deviceId;

        $output = $this->runCommand('patch-rsa-device', [
            'registry' => self::$registryId,
            'device' => $deviceId,
            'certificate-file' => __DIR__ . '/data/rsa_cert.pem',
        ]);

        $this->assertContains('Updated device', $output);
    }

    private static function runCommand($commandName, $args = [])
    {
        $application = require __DIR__ . '/../iot.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        try {
            $commandTester->execute(
                $args,
                ['interactive' => false]
            );
        } catch (\Exception $e) {
            print($e->getMessage() . PHP_EOL);
        }

        return ob_get_clean();
    }
}
