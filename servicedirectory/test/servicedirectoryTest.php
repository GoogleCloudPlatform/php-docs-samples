<?php

/**
 * Copyright 2020 Google Inc.
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
namespace Google\Cloud\Samples\ServiceDirectory;

use Google\Cloud\ServiceDirectory\V1beta1\Endpoint;
use Google\Cloud\ServiceDirectory\V1beta1\RegistrationServiceClient;
use Google\Cloud\ServiceDirectory\V1beta1\Service;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for Service Directory commands.
 */
class servicedirectoryTest extends TestCase
{
    use TestTrait;

    private static $locationId = 'us-east1';

    // public static function setUpBeforeClass()
    // {
    //   $this->requireEnv('GOOGLE_APPLICATION_CREDENTIALS');
    //   $this->requireEnv('GOOGLE_PROJECT_ID');
    // }

    public static function tearDownAfterClass()
    {
        // Delete any namespaces created during the tests.
        $client = new RegistrationServiceClient();
        $pagedResponse = $client->listNamespaces(RegistrationServiceClient::locationName(self::$projectId, self::$locationId));
        foreach ($pagedResponse->iterateAllElements() as $namespace_pb) {
            $client->deleteNamespace($namespace_pb->getName());
        }
    }

    public function testNamespaces()
    {
        $namespaceId = uniqid('sd-php-namespace-');
        $namespaceFqdn = sprintf('projects/%s/locations/%s/namespaces/%s', self::$projectId, self::$locationId, $namespaceId);

        $output = $this->runSnippet('create_namespace', [
          self::$projectId,
          self::$locationId,
          $namespaceId
      ]);
        $this->assertContains('Created Namespace: ' . $namespaceFqdn, $output);

        $output = $this->runSnippet('delete_namespace', [
          self::$projectId,
          self::$locationId,
          $namespaceId
      ]);
        $this->assertContains('Deleted Namespace: ' . $namespaceFqdn, $output);
    }

    public function testServices()
    {
        $namespaceId = uniqid('sd-php-namespace-');
        $serviceId = uniqid('sd-php-service-');
        $serviceFqdn = sprintf('projects/%s/locations/%s/namespaces/%s/services/%s', self::$projectId, self::$locationId, $namespaceId, $serviceId);

        // Setup: create a namespace for the service to live in.
        $this->runSnippet('create_namespace', [
          self::$projectId,
          self::$locationId,
          $namespaceId
      ]);

        $output = $this->runSnippet('create_service', [
          self::$projectId,
          self::$locationId,
          $namespaceId,
          $serviceId
      ]);
        $this->assertContains('Created Service: ' . $serviceFqdn, $output);

        $output = $this->runSnippet('delete_service', [
          self::$projectId,
          self::$locationId,
          $namespaceId,
          $serviceId
      ]);
        $this->assertContains('Deleted Service: ' . $serviceFqdn, $output);
    }

    public function testEndpoints()
    {
        $namespaceId = uniqid('sd-php-namespace-');
        $serviceId = uniqid('sd-php-service-');
        $endpointId = uniqid('sd-php-endpoint-');
        $endpointFqdn = sprintf('projects/%s/locations/%s/namespaces/%s/services/%s/endpoints/%s', self::$projectId, self::$locationId, $namespaceId, $serviceId, $endpointId);
        $ip = "1.2.3.4";
        $port = 8080;

        // Setup: create a namespace and service for the service to live in.
        $this->runSnippet('create_namespace', [
          self::$projectId,
          self::$locationId,
          $namespaceId
      ]);
        $this->runSnippet('create_service', [
          self::$projectId,
          self::$locationId,
          $namespaceId,
          $serviceId
      ]);

        $output = $this->runSnippet('create_endpoint', [
          self::$projectId,
          self::$locationId,
          $namespaceId,
          $serviceId,
          $endpointId,
          $ip,
          $port
      ]);
        $this->assertContains('Created Endpoint: ' . $endpointFqdn, $output);
        $this->assertContains('IP: ' . $ip, $output);
        $this->assertContains('Port: ' . $port, $output);

        $output = $this->runSnippet('delete_endpoint', [
          self::$projectId,
          self::$locationId,
          $namespaceId,
          $serviceId,
          $endpointId
      ]);
        $this->assertContains('Deleted Endpoint: ' . $endpointFqdn, $output);
    }

    public function testResolveService()
    {
        $namespaceId = uniqid('sd-php-namespace-');
        $serviceId = uniqid('sd-php-service-');
        $serviceFqdn = sprintf('projects/%s/locations/%s/namespaces/%s/services/%s', self::$projectId, self::$locationId, $namespaceId, $serviceId);
        $endpointId = uniqid('sd-php-endpoint-');
        $endpointFqdn = sprintf('%s/endpoints/%s', $serviceFqdn, $endpointId);
        $ip = "1.2.3.4";
        $port = 8080;

        // Setup: create a namespace, service, and endpoint.
        $this->runSnippet('create_namespace', [
          self::$projectId,
          self::$locationId,
          $namespaceId
      ]);
        $this->runSnippet('create_service', [
          self::$projectId,
          self::$locationId,
          $namespaceId,
          $serviceId
      ]);
        $this->runSnippet('create_endpoint', [
          self::$projectId,
          self::$locationId,
          $namespaceId,
          $serviceId,
          $endpointId,
          $ip,
          $port
      ]);

        $output = $this->runSnippet('resolve_service', [
          self::$projectId,
          self::$locationId,
          $namespaceId,
          $serviceId
      ]);
        $this->assertContains('Resolved Service: ' . $serviceFqdn, $output);
        $this->assertContains('Name: ' . $endpointFqdn, $output);
        $this->assertContains('IP: ' . $ip, $output);
        $this->assertContains('Port: ' . $port, $output);
    }

    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([0], array_values($params));
        $argc = count($argv);
        ob_start();
        require __DIR__ . "/../src/$sampleName.php";
        return ob_get_clean();
    }
}
