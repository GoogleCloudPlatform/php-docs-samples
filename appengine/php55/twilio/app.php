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

use Silex\Application;

$app = new Application();

$app->get('/', function () use ($app) {
    if ($app['twilio.account_sid'] == 'TWILIO_ACCOUNT_SID') {
        return 'set your Twilio SID and Auth Token in <code>index.php</code>';
    }
    $sid = $app['twilio.account_sid'];
    $token = $app['twilio.auth_token'];
    $fromNumber = $app['twilio.from_number'];
    $toNumber = $app['twilio.to_number'];

    # [START send_sms]
    $client = new Services_Twilio($sid, $token);
    $sms = $client->account->messages->sendMessage(
        $fromNumber, // From this number
        $toNumber,   // Send to this number
        'Hello monkey!!'
    );

    return sprintf('Message ID: %s, Message Body: %s', $sms->sid, $sms->body);
    # [END send_sms]
});

$app->post('/twiml', function () {
    # [START twiml]
    $response = new Services_Twilio_Twiml();
    $response->say('Hello Monkey');

    return (string) $response;
    # [END twiml]
});

return $app;
