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

use Google\Cloud\ServiceDirectory\V1\Client\RegistrationServiceClient;
use Google\Cloud\ServiceDirectory\V1\DeleteNamespaceRequest;
use Google\Cloud\ServiceDirectory\V1\Endpoint;
use Google\Cloud\ServiceDirectory\V1\ListNamespacesRequest;
use Google\Cloud\ServiceDirectory\V1\Service;
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
        $listNamespacesRequest = (new ListNamespacesRequest())
            ->setParent(RegistrationServiceClient::locationName(self::$projectId, self::$locationId));
        $pagedResponse = $client->listNamespaces($listNamespacesRequest);
        foreach ($pagedResponse->iterateAllElements() as $namespace_pb) {
            $deleteNamespaceRequest = (new DeleteNamespaceRequest())
                ->setName($namespace_pb->getName());
            $client->deleteNamespace($deleteNamespaceRequest);
        }
    }

    public function testNamespaces()
    {
        $namespaceId = uniqid('sd-php-namespace-');
        $namespaceName = sprintf('projects/%s/locations/%s/namespaces/%s', self::$projectId, self::$locationId, $namespaceId);

        $output = $this->runFunctionSnippet('create_namespace', [
            self::$projectId,
            self::$locationId,
            $namespaceId
        ]);
        $this->assertStringContainsString('Created Namespace: ' . $namespaceName, $output);

        $output = $this->runFunctionSnippet('delete_namespace', [
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
        $output = $this->runFunctionSnippet('create_namespace', [
            self::$projectId,
            self::$locationId,
            $namespaceId
        ]);
        $this->assertStringContainsString('Created Namespace: ' . $namespaceName, $output);
        $output = $this->runFunctionSnippet('create_service', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId
        ]);
        $this->assertStringContainsString('Created Service: ' . $serviceName, $output);

        $output = $this->runFunctionSnippet('delete_service', [
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
        $output = $this->runFunctionSnippet('create_namespace', [
            self::$projectId,
            self::$locationId,
            $namespaceId
        ]);
        $this->assertStringContainsString('Created Namespace: ' . $namespaceName, $output);
        $output = $this->runFunctionSnippet('create_service', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId
        ]);
        $this->assertStringContainsString('Created Service: ' . $serviceName, $output);

        $output = $this->runFunctionSnippet('create_endpoint', [
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

        $output = $this->runFunctionSnippet('delete_endpoint', [
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
        $output = $this->runFunctionSnippet('create_namespace', [
            self::$projectId,
            self::$locationId,
            $namespaceId
        ]);
        $this->assertStringContainsString('Created Namespace: ' . $namespaceName, $output);
        $output = $this->runFunctionSnippet('create_service', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId
        ]);
        $this->assertStringContainsString('Created Service: ' . $serviceName, $output);
        $output = $this->runFunctionSnippet('create_endpoint', [
            self::$projectId,
            self::$locationId,
            $namespaceId,
            $serviceId,
            $endpointId,
            $ip,
            $port
        ]);
        $this->assertStringContainsString('Created Endpoint: ' . $endpointName, $output);

        $output = $this->runFunctionSnippet('resolve_service', [
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
