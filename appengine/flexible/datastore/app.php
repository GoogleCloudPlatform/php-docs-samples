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

use Google\Cloud\Datastore\DatastoreClient;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use RKA\Middleware\IpAddress;
use Slim\Factory\AppFactory;

// Create App
$app = AppFactory::create();

// Display errors
$app->addErrorMiddleware(true, true, true);

// Add IP address middleware
$checkProxyHeaders = true;
$trustedProxies = ['10.0.0.1', '10.0.0.2'];
$app->add(new IpAddress($checkProxyHeaders, $trustedProxies));

$app->get('/', function (Request $request, Response $response) {
    $projectId = getenv('GCLOUD_PROJECT');
    if (empty($projectId)) {
        $response->getBody()->write('Set the GCLOUD_PROJECT environment variable to run locally');
        return $response;
    }

    # [START gae_flex_datastore_client]
    $datastore = new DatastoreClient([
        'projectId' => $projectId
    ]);
    # [END gae_flex_datastore_client]

    // determine the user's IP
    $user_ip = get_user_ip($request);

    # [START gae_flex_datastore_entity]
    // Create an entity to insert into datastore.
    $key = $datastore->key('visit');
    $entity = $datastore->entity($key, [
        'user_ip' => $user_ip,
        'timestamp' => new DateTime(),
    ]);
    $datastore->insert($entity);
    # [END gae_flex_datastore_entity]

    # [START gae_flex_datastore_query]
    // Query recent visits.
    $query = $datastore->query()
        ->kind('visit')
        ->order('timestamp', 'DESCENDING')
        ->limit(10);
    $results = $datastore->runQuery($query);
    $visits = [];
    foreach ($results as $entity) {
        $visits[] = sprintf('Time: %s Addr: %s',
            $entity['timestamp']->format('Y-m-d H:i:s'),
            $entity['user_ip']);
    }
    # [END gae_flex_datastore_query]
    array_unshift($visits, 'Last 10 visits:');
    $response->getBody()->write(implode("\n", $visits));

    return $response
        ->withStatus(200)
        ->withHeader('Content-Type', 'text/plain');
});

function get_user_ip(Request $request)
{
    $ip = $request->getAttribute('ip_address');
    // Keep only the first two octets of the IP address.
    $octets = explode($separator = ':', $ip);
    if (count($octets) < 2) {  // Must be ip4 address
        $octets = explode($separator = '.', $ip);
    }
    if (count($octets) < 2) {
        $octets = ['bad', 'ip'];
    }
    // Replace empty chunks with zeros.
    $octets = array_map(function ($x) {
        return $x == '' ? '0' : $x;
    }, $octets);
    $user_ip = $octets[0] . $separator . $octets[1];
    return $user_ip;
}

return $app;
