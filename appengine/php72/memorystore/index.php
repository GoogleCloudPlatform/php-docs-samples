<?php
/**
 * Copyright 2019 Google Inc.
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

// Only serve traffic from "/"
switch (@parse_url($_SERVER['REQUEST_URI'])['path']) {
    case '/':
        break;
    default:
        http_response_code(404);
        exit('Not Found');
}

// Connect to Memorystore from App Engine.
if (!$host = getenv('REDIS_HOST')) {
    throw new Exception('The REDIS_HOST environment variable is required');
}

# Memorystore Redis port defaults to 6379
$port = getenv('REDIS_PORT') ?: '6379';

try {
    $redis = new Redis();
    $redis->connect($host, $port);
} catch (Exception $e) {
    return print('Error: ' . $e->getMessage());
}

$value = $redis->incr('counter');

printf('Visitor number: %s', $value);
