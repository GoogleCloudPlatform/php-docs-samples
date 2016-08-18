<?php
/**
 * Copyright 2015 Google Inc.
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

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// create the Silex application
$app = new Application();

$app['datastore'] = function () {
    // Datastore API has intermittent failures, so we set the
    // Google Client to retry in the event of a 503 Backend Error
    $retryConfig = ['retries' => 2 ];
    $client = new \Google_Client(['retry' => $retryConfig ]);
    $client->setScopes([
        Google_Service_Datastore::CLOUD_PLATFORM,
        Google_Service_Datastore::DATASTORE,
    ]);
    $client->useApplicationDefaultCredentials();
    return new \Google_Service_Datastore($client);
};

$app->get('/', function (Application $app, Request $request) {
    /** @var \Google_Service_Datastore $datastore */
    $datastore = $app['datastore'];
    $ip = $request->GetClientIp();
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
    // Create an entity to insert into datastore.
    $key = new \Google_Service_Datastore_Key(['path' => ['kind' => 'visit']]);
    $date = new DateTime();
    $date->setTimezone(new DateTimeZone('UTC'));
    $properties = [
        'user_ip' => ['stringValue' => $user_ip],
        'timestamp' => ['timestampValue' => $date->format("Y-m-d\TH:i:s\Z")]
    ];
    $entity = new \Google_Service_Datastore_Entity([
        'key' => $key,
        'properties' => $properties
    ]);

    // Use "NON_TRANSACTIONAL" for simplicity.  However, it means that we may
    // not see this result in the query below.
    $request = new \Google_Service_Datastore_CommitRequest([
        'mode' => 'NON_TRANSACTIONAL',
        'mutations' => [
            [
                'insert' => $entity,
            ]
        ]
    ]);
    $dataset_id = $app['google.dataset_id'];
    $datastore->projects->commit($dataset_id, $request);

    $query = new \Google_Service_Datastore_Query([
        'kind' => [
            [
                'name' => 'visit',
            ],
        ],
        'order' => [
            'property' => [
                'name' => 'timestamp',
            ],
            'direction' => 'DESCENDING',
        ],
        'limit' => 10,
    ]);
    $request = new \Google_Service_Datastore_RunQueryRequest();
    $request->setQuery($query);
    $response = $datastore->projects->runQuery($dataset_id, $request);
    /** @var \Google_Service_Datastore_QueryResultBatch $batch */
    $batch = $response->getBatch();
    $visits = ["Last 10 visits:"];
    foreach ($batch->getEntityResults() as $entityResult) {
        $properties = $entityResult->getEntity()->getProperties();
        array_push($visits, sprintf('Time: %s Addr: %s',
            $properties['timestamp']['timestampValue'],
            $properties['user_ip']['stringValue']));
    }
    return new Response(implode("\n", $visits), 200,
        ['Content-Type' => 'text/plain']);
});

return $app;
