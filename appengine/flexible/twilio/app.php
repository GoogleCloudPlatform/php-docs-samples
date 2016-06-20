<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
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

$app = new Application();

$app['twilio'] = function ($app) {
    return new Services_Twilio(
        $app['twilio.account_sid'],
        $app['twilio.auth_token']
    );
};

/***
 * Answers a call and replies with a simple greeting.
 */
$app->post('/call/receive', function () use ($app) {
    $response = new Services_Twilio_Twiml();
    $response->say('Hello from Twilio!');
    return new Response(
        (string)$response,
        200,
        ['Content-Type' => 'application/xml']
    );
});


/***
 * Send an sms.
 */
$app->post('/sms/send', function (Request $request) use ($app) {
    /** @var Services_Twilio $twilio */
    $twilio = $app['twilio'];
    $sms = $twilio->account->messages->sendMessage(
        $app['twilio.number'], // From this number
        $request->get('to'),   // Send to this number
        'Hello from Twilio!'
    );

    return sprintf('Message ID: %s, Message Body: %s', $sms->sid, $sms->body);
});

/***
 * Receive an sms.
 */
$app->post('/sms/receive', function (Request $request) use ($app) {
    $sender = $request->get('From');
    $body = $request->get('Body');
    $message = "Hello, $sender, you said: $body";

    $response = new Services_Twilio_Twiml();
    $response->message($message);
    return new Response(
        (string) $response,
        200,
        ['Content-Type' => 'application/xml']
    );
});

return $app;
