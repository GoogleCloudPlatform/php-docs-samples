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

require 'vendor/autoload.php';

use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnitRetry\RetryTrait;

/**
 * Unit Tests for iot commands.
 */
class iotTest extends TestCase
{
    use TestTrait, ExecuteCommandTrait, RetryTrait;

    private static $commandFile = __DIR__ . '/../iot.php';
    private static $testId;
    private static $registryId;
    private static $devices = [];
    private static $gateways = [];

    public static function setUpBeforeClass(): void
    {
        self::$testId = time() . '-' . rand();
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$devices as $deviceId) {
            printf('Cleaning up Device %s' . PHP_EOL, $deviceId);
            self::runCommand('delete-device', [
                'registry' => self::$registryId,
                'device' => $deviceId,
            ]);
        }
        foreach (self::$gateways as $gatewayId) {
            printf('Cleaning up Gateway %s' . PHP_EOL, $gatewayId);
            self::runCommand('delete-gateway', [
                'registry' => self::$registryId,
                'gateway' => $gatewayId,
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
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $registryId = 'test-registry-' . self::$testId;

        $output = $this->runCommand('create-registry', [
            'registry' => $registryId,
            'pubsub-topic' => $topic,
        ]);
        self::$registryId = $registryId;
        $this->assertStringContainsString('Id: ' . $registryId, $output);
    }

    /** @depends testCreateRegistry */
    public function testListRegistries()
    {
        $output = $this->runCommand('list-registries');
        $this->assertStringContainsString(self::$registryId, $output);
    }

    /** @depends testCreateRegistry */
    public function testGetRegistry()
    {
        $output = $this->runCommand('get-registry', [
            'registry' => self::$registryId,
        ]);
        $this->assertStringContainsString(self::$registryId, $output);
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
        $this->assertStringContainsString($email, $output);

        $output = $this->runCommand('get-iam-policy', [
            'registry' => self::$registryId,
        ]);
        $this->assertStringContainsString($email, $output);
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
        $this->assertStringContainsString($deviceId, $output);
    }

    /** @depends testCreateRsaDevice */
    public function testSetDeviceState()
    {
        $certB64 = $this->requireEnv('GOOGLE_IOT_DEVICE_CERTIFICATE_B64');
        $iotCert = base64_decode($certB64);
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
        $this->assertStringContainsString('Data: ' . $data, $output);
    }

    /** @depends testCreateRsaDevice */
    public function testListDevices()
    {
        $output = $this->runCommand('list-devices', [
            'registry' => self::$registryId,
        ]);
        $this->assertStringContainsString(self::$devices[0], $output);
    }

    /** @depends testCreateRsaDevice */
    public function testGetDevice()
    {
        $output = $this->runCommand('get-device', [
            'registry' => self::$registryId,
            'device' => self::$devices[0],
        ]);
        $this->assertStringContainsString(self::$devices[0], $output);
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
        $this->assertStringContainsString('Version: 2', $output);
        $this->assertStringContainsString('Data: ' . $config, $output);
    }

    /** @depends testCreateRsaDevice */
    public function testSendCommandToDevice()
    {
        $command = '{"data":"example of command data"}';
        $output = $this->runCommand('send-command-to-device', [
            'registry' => self::$registryId,
            'device' => self::$devices[0],
            'command-data' => $command,
        ]);
        print($output);
        $this->assertStringContainsString('Sending command to', $output);
    }

    /** @depends testSetDeviceConfig */
    public function testGetDeviceConfigs()
    {
        $output = $this->runCommand('get-device-configs', [
            'registry' => self::$registryId,
            'device' => self::$devices[0],
        ]);
        $this->assertStringContainsString('Version: 2', $output);
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
        $this->assertStringContainsString($deviceId, $output);
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
        $this->assertStringContainsString($deviceId, $output);
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

        $this->assertStringContainsString('Updated device', $output);
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

        $this->assertStringContainsString('Updated device', $output);
    }

    /** @depends testCreateRegistry */
    public function testCreateGateway()
    {
        $gatewayId = 'test-rsa-gateway' . self::$testId;

        $output = $this->runCommand('create-gateway', [
            'registry' => self::$registryId,
            'gateway' => $gatewayId,
            'certificate-file' => __DIR__ . '/data/rsa_cert.pem',
            'algorithm' => 'RS256',
        ]);
        self::$gateways[] = $gatewayId;
        $this->assertStringContainsString('Gateway: ', $output);

        $output = $this->runCommand('list-gateways', [
            'registry' => self::$registryId
        ]);
        $this->assertStringContainsString($gatewayId, $output);
    }

    /**
     * @depends testCreateGateway
     * @retryAttempts 3
     */
    public function testBindUnbindDevice()
    {
        $deviceId = 'test-device-to-bind' . self::$testId;
        $gatewayId = 'test-bindunbind-gateway' . self::$testId;

        $this->runCommand('create-gateway', [
            'registry' => self::$registryId,
            'gateway' => $gatewayId,
            'certificate-file' => __DIR__ . '/data/rsa_cert.pem',
            'algorithm' => 'RS256',
        ]);
        self::$gateways[] = $gatewayId;

        $this->runCommand('create-unauth-device', [
            'registry' => self::$registryId,
            'device' => $deviceId,
        ]);
        self::$devices[] = $deviceId;

        $output = $this->runCommand('bind-device-to-gateway', [
            'registry' => self::$registryId,
            'gateway' => $gatewayId,
            'device' => $deviceId,
        ]);
        $this->assertStringContainsString('Device bound', $output);

        $output = $this->runCommand('unbind-device-from-gateway', [
            'registry' => self::$registryId,
            'gateway' => $gatewayId,
            'device' => $deviceId,
        ]);
        $this->assertStringContainsString('Device unbound', $output);
    }

    /** @depends testBindUnbindDevice */
    public function testListDevicesForGateway()
    {
        $deviceId = 'php-bind-and-list' . self::$testId;
        $gatewayId = 'php-bal-gateway' . self::$testId;

        $this->runCommand('create-unauth-device', [
            'registry' => self::$registryId,
            'device' => $deviceId,
        ]);
        self::$devices[] = $deviceId;

        $this->runCommand('create-gateway', [
            'registry' => self::$registryId,
            'gateway' => $gatewayId,
            'certificate-file' => __DIR__ . '/data/rsa_cert.pem',
            'algorithm' => 'RS256',
        ]);
        self::$gateways[] = $gatewayId;

        $this->runCommand('bind-device-to-gateway', [
            'registry' => self::$registryId,
            'gateway' => $gatewayId,
            'device' => $deviceId,
        ]);

        $output = $this->runCommand('list-devices-for-gateway', [
            'registry' => self::$registryId,
            'gateway' => $gatewayId,
        ]);
        $this->assertStringContainsString($deviceId, $output);

        $this->runCommand('unbind-device-from-gateway', [
            'registry' => self::$registryId,
            'gateway' => $gatewayId,
            'device' => $deviceId,
        ]);
    }
}
