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

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    /** @var Mailjet\Client $mailjet */
    $mailjet = $app['mailjet'];
    return <<<EOF
<!doctype html>
<html><body>
<form method="POST" target="/send">
<input type="text" name="recipient" placeholder="Enter recipient email">
<input type="submit" name="submit" value="Send email">
</form>
</body></html>
EOF;
});

$app->post('/send', function () use ($app) {
    /** @var Symfony\Component\HttpFoundation\Request $request */
    $request = $app['request'];
    /** @var Mailjet\Client $mailjet */
    $mailjet = $app['mailjet'];
    $recipient = $request->get('recipient');
    $sender = $app['mailjet.sender'] ?: "test@example.com";

    # [START gae_mailjet_send_message]
    $body = [
        'FromEmail' => $sender,
        'FromName' => "Testing Mailjet",
        'Subject' => "Your email flight plan!",
        'Text-part' => "Dear passenger, welcome to Mailjet! May the delivery force be with you!",
        'Html-part' => "<h3>Dear passenger, welcome to Mailjet!</h3><br/>May the delivery force be with you!",
        'Recipients' => [
            [
                'Email' => $recipient,
            ]
        ]
    ];

    // trigger the API call
    $response = $mailjet->post(Mailjet\Resources::$Email, ['body' => $body]);
    if ($response->success()) {
        // if the call succed, data will go here
        return sprintf(
            '<pre>%s</pre>',
            json_encode($response->getData(), JSON_PRETTY_PRINT)
        );
    }

    return 'Error: ' . print_r($response->getStatus(), true);
    # [END gae_mailjet_send_message]
});

$app['mailjet'] = function () use ($app) {
    if ($app['mailjet.api_key'] == 'MAILJET_APIKEY') {
        return 'set your mailjet api key and secret in <code>index.php</code>';
    }
    $mailjetApiKey = $app['mailjet.api_key'];
    $mailjetSecret = $app['mailjet.secret'];

    # [START gae_mailjet_import]
    $mailjet = new Mailjet\Client($mailjetApiKey, $mailjetSecret);
    # [END gae_mailjet_import]

    return $mailjet;
};

return $app;
