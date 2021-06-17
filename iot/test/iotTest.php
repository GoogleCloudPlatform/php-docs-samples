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

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnitRetry\RetryTrait;

/**
 * Unit Tests for iot commands.
 */
class iotTest extends TestCase
{
    use TestTrait;
    use RetryTrait;

    const LOCATION = 'us-central1';

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
            self::runFunctionSnippet('delete_device', [
                self::$registryId,
                $deviceId,
                self::$projectId,
                self::LOCATION,
            ]);
        }
        foreach (self::$gateways as $gatewayId) {
            printf('Cleaning up Gateway %s' . PHP_EOL, $gatewayId);
            self::runFunctionSnippet('delete_gateway', [
                self::$registryId,
                $gatewayId,
                self::$projectId,
                self::LOCATION,
            ]);
        }
        if (self::$registryId) {
            printf('Cleaning up Registry %s' . PHP_EOL, self::$registryId);
            self::runFunctionSnippet('delete_registry', [
                self::$registryId,
                self::$projectId,
                self::LOCATION,
            ]);
        }
    }

    public function testCreateRegistry()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $registryId = 'test-registry-' . self::$testId;

        $output = $this->runFunctionSnippet('create_registry', [
            $registryId,
            $topic,
            self::$projectId,
            self::LOCATION,
        ]);
        self::$registryId = $registryId;
        $this->assertStringContainsString('Id: ' . $registryId, $output);
    }

    /** @depends testCreateRegistry */
    public function testListRegistries()
    {
        $output = $this->runFunctionSnippet('list_registries', [
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString(self::$registryId, $output);
    }

    /** @depends testCreateRegistry */
    public function testGetRegistry()
    {
        $output = $this->runFunctionSnippet('get_registry', [
            self::$registryId,
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString(self::$registryId, $output);
    }

    /** @depends testCreateRegistry */
    public function testIamPolicy()
    {
        $email = 'betterbrent@google.com';
        $output = $this->runFunctionSnippet('set_iam_policy', [
            self::$registryId,
            'roles/viewer',
            'user:' . $email,
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString($email, $output);

        $output = $this->runFunctionSnippet('get_iam_policy', [
            self::$registryId,
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString($email, $output);
    }

    /** @depends testCreateRegistry */
    public function testCreateRsaDevice()
    {
        $deviceId = 'test-rsa_device-' . self::$testId;

        $output = $this->runFunctionSnippet('create_rsa_device', [
            self::$registryId,
            $deviceId,
            __DIR__ . '/data/rsa_cert.pem',
            self::$projectId,
            self::LOCATION,
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
        $output = $this->runFunctionSnippet('set_device_state', [
            self::$registryId,
            self::$devices[0],
            $iotCertFile,
            $data,
            self::$projectId,
            self::LOCATION,
        ]);

        $output = $this->runFunctionSnippet('get_device_state', [
            self::$registryId,
            self::$devices[0],
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString('Data: ' . $data, $output);
    }

    /** @depends testCreateRsaDevice */
    public function testListDevices()
    {
        $output = $this->runFunctionSnippet('list_devices', [
            self::$registryId,
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString(self::$devices[0], $output);
    }

    /** @depends testCreateRsaDevice */
    public function testGetDevice()
    {
        $output = $this->runFunctionSnippet('get_device', [
            self::$registryId,
            self::$devices[0],
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString(self::$devices[0], $output);
    }

    /** @depends testCreateRsaDevice */
    public function testSetDeviceConfig()
    {
        $config = '{"data":"example of config data"}';
        $output = $this->runFunctionSnippet('set_device_config', [
            self::$registryId,
            self::$devices[0],
            $config,
            null,
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString('Version: 2', $output);
        $this->assertStringContainsString('Data: ' . $config, $output);
    }

    /** @depends testCreateRsaDevice */
    public function testSendCommandToDevice()
    {
        $command = '{"data":"example of command data"}';
        $output = $this->runFunctionSnippet('send_command_to_device', [
            self::$registryId,
            self::$devices[0],
            $command,
            self::$projectId,
            self::LOCATION,
        ]);
        print($output);
        $this->assertStringContainsString('Sending command to', $output);
    }

    /** @depends testSetDeviceConfig */
    public function testGetDeviceConfigs()
    {
        $output = $this->runFunctionSnippet('get_device_configs', [
            self::$registryId,
            self::$devices[0],
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString('Version: 2', $output);
    }

    /** @depends testCreateRegistry */
    public function testCreateEsDevice()
    {
        $deviceId = 'test-es_device-' . self::$testId;

        $output = $this->runFunctionSnippet('create_es_device', [
            self::$registryId,
            $deviceId,
            __DIR__ . '/data/ec_public.pem',
            self::$projectId,
            self::LOCATION,
        ]);
        self::$devices[] = $deviceId;
        $this->assertStringContainsString($deviceId, $output);
    }

    /** @depends testCreateRegistry */
    public function testCreateUnauthDevice()
    {
        $deviceId = 'test-unauth_device-' . self::$testId;

        $output = $this->runFunctionSnippet('create_unauth_device', [
            self::$registryId,
            $deviceId,
            self::$projectId,
            self::LOCATION,
        ]);
        self::$devices[] = $deviceId;
        $this->assertStringContainsString($deviceId, $output);
    }

    /** @depends testCreateUnauthDevice */
    public function testPatchEs()
    {
        $deviceId = 'test-es_device_to_patch' . self::$testId;

        $this->runFunctionSnippet('create_unauth_device', [
            self::$registryId,
            $deviceId,
            self::$projectId,
            self::LOCATION,
        ]);
        self::$devices[] = $deviceId;

        $output = $this->runFunctionSnippet('patch_es', [
            self::$registryId,
            $deviceId,
            __DIR__ . '/data/ec_public.pem',
            self::$projectId,
            self::LOCATION,
        ]);

        $this->assertStringContainsString('Updated device', $output);
    }

    /** @depends testCreateRegistry */
    public function testPatchRsa()
    {
        $deviceId = 'test-rsa_device_to_patch' . self::$testId;

        $this->runFunctionSnippet('create_unauth_device', [
            self::$registryId,
            $deviceId,
            self::$projectId,
            self::LOCATION,
        ]);
        self::$devices[] = $deviceId;

        $output = $this->runFunctionSnippet('patch_rsa', [
            self::$registryId,
            $deviceId,
            __DIR__ . '/data/rsa_cert.pem',
            self::$projectId,
            self::LOCATION,
        ]);

        $this->assertStringContainsString('Updated device', $output);
    }

    /** @depends testCreateRegistry */
    public function testCreateGateway()
    {
        $gatewayId = 'test-rsa-gateway' . self::$testId;

        $output = $this->runFunctionSnippet('create_gateway', [
            self::$registryId,
            $gatewayId,
            __DIR__ . '/data/rsa_cert.pem',
            'RS256',
            self::$projectId,
            self::LOCATION,
        ]);
        self::$gateways[] = $gatewayId;
        $this->assertStringContainsString('Gateway: ', $output);

        $output = $this->runFunctionSnippet('list_gateways', [
            self::$registryId,
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString($gatewayId, $output);
    }

    /**
     * @depends testCreateGateway
     * @retryAttempts 3
     */
    public function testBindUnbindDevice()
    {
        $deviceId = 'test_device_to_bind' . self::$testId;
        $gatewayId = 'test-bindunbind-gateway' . self::$testId;

        $this->runFunctionSnippet('create_gateway', [
            self::$registryId,
            $gatewayId,
            __DIR__ . '/data/rsa_cert.pem',
            'RS256',
            self::$projectId,
            self::LOCATION,
        ]);
        self::$gateways[] = $gatewayId;

        $this->runFunctionSnippet('create_unauth_device', [
            self::$registryId,
            $deviceId,
            self::$projectId,
            self::LOCATION,
        ]);
        self::$devices[] = $deviceId;

        $output = $this->runFunctionSnippet('bind_device_to_gateway', [
            self::$registryId,
            $gatewayId,
            $deviceId,
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString('Device bound', $output);

        $output = $this->runFunctionSnippet('unbind_device_from_gateway', [
            self::$registryId,
            $gatewayId,
            $deviceId,
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString('Device unbound', $output);
    }

    /** @depends testBindUnbindDevice */
    public function testListDevicesForGateway()
    {
        $deviceId = 'php-bind-and-list' . self::$testId;
        $gatewayId = 'php-bal-gateway' . self::$testId;

        $this->runFunctionSnippet('create_unauth_device', [
            self::$registryId,
            $deviceId,
            self::$projectId,
            self::LOCATION,
        ]);
        self::$devices[] = $deviceId;

        $this->runFunctionSnippet('create_gateway', [
            self::$registryId,
            $gatewayId,
            __DIR__ . '/data/rsa_cert.pem',
            'RS256',
            self::$projectId,
            self::LOCATION,
        ]);
        self::$gateways[] = $gatewayId;

        $this->runFunctionSnippet('bind_device_to_gateway', [
            self::$registryId,
            $gatewayId,
            $deviceId,
            self::$projectId,
            self::LOCATION,
        ]);

        $output = $this->runFunctionSnippet('list_devices_for_gateway', [
            self::$registryId,
            $gatewayId,
            self::$projectId,
            self::LOCATION,
        ]);
        $this->assertStringContainsString($deviceId, $output);

        $this->runFunctionSnippet('unbind_device_from_gateway', [
            self::$registryId,
            $gatewayId,
            $deviceId,
            self::$projectId,
            self::LOCATION,
        ]);
    }
}
