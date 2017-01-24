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

# [START example]
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// create the Silex application
$app = new Application();

$app['pdo'] = function ($app) {
    $pdo = new PDO(
        $app['mysql.dsn'],
        $app['mysql.user'],
        $app['mysql.password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query('CREATE TABLE IF NOT EXISTS visits ' .
        '(time_stamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, user_ip CHAR(64))');
    return $pdo;
};

$app->get('/', function (Application $app, Request $request) {
    $ip = $request->GetClientIp();
    // Keep only the first two octets of the IP address.
    $octets = explode($separator = ':', $ip);
    if (count($octets) < 2) {  // Must be ip4 address
        $octets = explode($separator = '.', $ip);
    }
    if (count($octets) < 2) {
        $octets = ['bad', 'ip'];  // IP address will be recorded as bad.ip.
    }
    // Replace empty chunks with zeros.
    $octets = array_map(function ($x) {
        return $x == '' ? '0' : $x;
    }, $octets);
    $user_ip = $octets[0] . $separator . $octets[1];

    // Insert a visit into the database.
    /** @var PDO $pdo */
    $pdo = $app['pdo'];
    $insert = $pdo->prepare('INSERT INTO visits (user_ip) values (:user_ip)');
    $insert->execute(['user_ip' => $user_ip]);

    // Look up the last 10 visits
    $select = $pdo->prepare(
        'SELECT * FROM visits ORDER BY time_stamp DESC LIMIT 10');
    $select->execute();
    $visits = ["Last 10 visits:"];
    while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
        array_push($visits, sprintf('Time: %s Addr: %s', $row['time_stamp'],
            $row['user_ip']));
    }
    return new Response(implode("\n", $visits), 200,
        ['Content-Type' => 'text/plain']);
});
# [END example]

return $app;
