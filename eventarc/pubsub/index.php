<?php
/**
 * Copyright 2020 Google LLC.
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

// [START eventarc_pubsub_server]
// [END eventarc_pubsub_server]

// [START eventarc_pubsub_handler]
$msg = "";

$headers = getallheaders();

$json = file_get_contents('php://input');
$body = json_decode($json);
if (empty($body)) {
  $msg .= "Bad Request: no Pub/Sub message received";
} else if (empty($body->message)) {
  $msg .= "Bad Request: invalid Pub/Sub message format";
} else {
  $name = base64_decode($body->message->data);
  $id = $headers["ce-id"];
  $msg .= "Hello, $name! ID: $id";
}
$msg .= "\n";

// Write to stderr for logging
$log = fopen('php://stderr', 'wb');
fwrite($log, $msg);
// Echo to return in request body
echo $msg;
// [END eventarc_pubsub_handler]
