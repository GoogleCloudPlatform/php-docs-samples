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
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use Twilio\Rest\Client as TwilioClient;
use Twilio\TwiML\VoiceResponse;
use Twilio\TwiML\MessagingResponse;

// Create App
$app = AppFactory::create();

// Display errors
$app->addErrorMiddleware(true, true, true);

$twilioAccountSid = getenv('TWILIO_ACCOUNT_SID');
$twilioAuthToken = getenv('TWILIO_AUTH_TOKEN');
$twilioNumber = getenv('TWILIO_FROM_NUMBER');

# [START gae_flex_twilio_receive_call]
/***
 * Answers a call and replies with a simple greeting.
 */
$app->post('/call/receive', function (Request $request, Response $response) {
    $twiml = new VoiceResponse();
    $twiml->say('Hello from Twilio!');
    $response->getBody()->write((string) $twiml);
    return $response
        ->withHeader('Content-Type', 'application/xml');
});
# [END gae_flex_twilio_receive_call]

# [START gae_flex_twilio_send_sms]
/***
 * Send an sms.
 */
$app->post('/sms/send', function (
    Request $request,
    Response $response
) use ($twilioAccountSid, $twilioAuthToken, $twilioNumber) {
    $twilio = new TwilioClient(
        $twilioAccountSid, // Your Twilio Account SID
        $twilioAuthToken   // Your Twilio Auth Token
    );
    parse_str((string) $request->getBody(), $postData);
    $sms = $twilio->messages->create(
        $postData['to'] ?? '', // to this number
        [
            'from' => $twilioNumber,   // from this number
            'body' => 'Hello from Twilio!',
        ]
    );
    $response->getBody()->write(
        sprintf('Message ID: %s, Message Body: %s', $sms->sid, $sms->body)
    );
    return $response;
});
# [END gae_flex_twilio_send_sms]

# [START gae_flex_twilio_receive_sms]
/***
 * Receive an sms.
 */
$app->post('/sms/receive', function (Request $request, Response $response) {
    parse_str((string) $request->getBody(), $postData);
    $sender = $postData['From'] ?? '';
    $body = $postData['Body'] ?? '';
    $message = "Hello, $sender, you said: $body";
    $twiml = new MessagingResponse();
    $twiml->message($message);
    $response->getBody()->write((string) $twiml);
    return $response
        ->withHeader('Content-Type', 'application/xml');
});
# [END gae_flex_twilio_receive_sms]

return $app;
