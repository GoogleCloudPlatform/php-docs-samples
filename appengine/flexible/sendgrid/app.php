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

 # [START gae_flex_sendgrid]
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    return <<<EOF
<!doctype html>
<html><body>
<form method="POST">
<input type="text" name="recipient" placeholder="Enter recipient email">
<input type="submit" name="submit">
</form>
</body></html>
EOF;
});

$app->post('/', function (Request $request) use ($app) {
    $sendgridSender = $app['sendgrid.sender'];
    $sendgridApiKey = $app['sendgrid.api_key'];
    $sendgridRecipient = $request->get('recipient');
    // $sendgridApiKey = 'YOUR_SENDGRID_APIKEY';
    // $sendgridSender = 'an-email-to-send-from@example.com';
    // $sendgridRecipient = 'some-recipient@example.com';
    $sender = new SendGrid\Email(null, $sendgridSender);
    $recipient = new SendGrid\Email(null, $sendgridRecipient);
    $subject = 'This is a test email';
    $body = new SendGrid\Content('text/plain', 'Example text body.');
    $mail = new SendGrid\Mail($sender, $subject, $recipient, $body);
    // send the email
    $sendgrid = new SendGrid($sendgridApiKey);
    $response = $sendgrid->client->mail()->send()->post($mail);
    if ($response->statusCode() < 200 || $response->statusCode() >= 300) {
        return new Response($response->body(), $response->statusCode());
    }
    return 'Email sent.';
});

return $app;
# [END gae_flex_sendgrid]
