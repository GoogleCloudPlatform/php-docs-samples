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

    public static function tearDownAfterClass(): void
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
        $namespaceName = sprintf('projects/%s/locations/%s/namespaces/%s', self::$projectId, self::$locationId, $namespaceId);

        $output = $this->runSnippet('create_namespace', [
            self::$projectId,
            self::$locationId,
            $namespaceId
        ]);
        $this->assertStringContainsString('Created Namespace: ' . $namespaceName, $output);

        $output = $this->runSnippet('delete_namespace', [
            self::$projectId,
            self::$locationId,
            $namespaceId
        ]);
        $this->assertStringContainsString('Deleted Namespace: ' . $namespaceName, $output);
    }

    public function testServices()
    {
        $namespaceId = uniqid('sd-php-namespace-');
        $namespaceName = sprintf('projects/%s/locations/%s/namespaces/%s', self::$projectId, self::$locationId, $namespaceId);
        $serviceId = uniqid('sd-php-service-');
        $serviceName = sprintf('%s/services/%s', $namespaceName, $serviceId);

        // Setup: create a namespace for the service to live in.
        $output = $this->runSnippet('create_namespace', [
            self::$projectId,
            self::$locationId,
            $namespaceId
        ]);
        $this->assertStringContainsString('Created Namespace: ' . $namespaceName, $output);
        $output = $this->runSnippet('create_service', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId
        ]);
        $this->assertStringContainsString('Created Service: ' . $serviceName, $output);

        $output = $this->runSnippet('delete_service', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId
        ]);
        $this->assertStringContainsString('Deleted Service: ' . $serviceName, $output);
    }

    public function testEndpoints()
    {
        $namespaceId = uniqid('sd-php-namespace-');
        $namespaceName = sprintf('projects/%s/locations/%s/namespaces/%s', self::$projectId, self::$locationId, $namespaceId);
        $serviceId = uniqid('sd-php-service-');
        $serviceName = sprintf('%s/services/%s', $namespaceName, $serviceId);
        $endpointId = uniqid('sd-php-endpoint-');
        $endpointName = sprintf('%s/endpoints/%s', $serviceName, $endpointId);
        $ip = '1.2.3.4';
        $port = 8080;

        // Setup: create a namespace and service for the service to live in.
        $output = $this->runSnippet('create_namespace', [
            self::$projectId,
            self::$locationId,
            $namespaceId
        ]);
        $this->assertStringContainsString('Created Namespace: ' . $namespaceName, $output);
        $output = $this->runSnippet('create_service', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId
        ]);
        $this->assertStringContainsString('Created Service: ' . $serviceName, $output);

        $output = $this->runSnippet('create_endpoint', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId,
            $endpointId,
            $ip,
            $port
        ]);
        $this->assertStringContainsString('Created Endpoint: ' . $endpointName, $output);
        $this->assertStringContainsString('IP: ' . $ip, $output);
        $this->assertStringContainsString('Port: ' . $port, $output);

        $output = $this->runSnippet('delete_endpoint', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId,
            $endpointId
        ]);
        $this->assertStringContainsString('Deleted Endpoint: ' . $endpointName, $output);
    }

    public function testResolveService()
    {
        $namespaceId = uniqid('sd-php-namespace-');
        $namespaceName = sprintf('projects/%s/locations/%s/namespaces/%s', self::$projectId, self::$locationId, $namespaceId);
        $serviceId = uniqid('sd-php-service-');
        $serviceName = sprintf('%s/services/%s', $namespaceName, $serviceId);
        $endpointId = uniqid('sd-php-endpoint-');
        $endpointName = sprintf('%s/endpoints/%s', $serviceName, $endpointId);
        $ip = '1.2.3.4';
        $port = 8080;

        // Setup: create a namespace, service, and endpoint.
        $output = $this->runSnippet('create_namespace', [
            self::$projectId,
            self::$locationId,
            $namespaceId
        ]);
        $this->assertStringContainsString('Created Namespace: ' . $namespaceName, $output);
        $output = $this->runSnippet('create_service', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId
        ]);
        $this->assertStringContainsString('Created Service: ' . $serviceName, $output);
        $output = $this->runSnippet('create_endpoint', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId,
            $endpointId,
            $ip,
            $port
        ]);
        $this->assertStringContainsString('Created Endpoint: ' . $endpointName, $output);

        $output = $this->runSnippet('resolve_service', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId
        ]);
        $this->assertStringContainsString('Resolved Service: ' . $serviceName, $output);
        $this->assertStringContainsString('Name: ' . $endpointName, $output);
        $this->assertStringContainsString('IP: ' . $ip, $output);
        $this->assertStringContainsString('Port: ' . $port, $output);
    }
}
