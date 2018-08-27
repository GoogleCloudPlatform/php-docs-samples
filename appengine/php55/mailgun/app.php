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
    if ($app['mailgun.domain'] == 'MAILGUN_DOMAIN') {
        return 'set your mailgun domain and API key in <code>index.php</code>';
    }

    return <<<EOF
<!doctype html>
<html><body>
<form method="POST">
<input type="text" name="recipient" placeholder="Enter recipient email">
<input type="submit" name="submit" value="simple">
<input type="submit" name="submit" value="complex">
</form>
</body></html>
EOF;
});

$app->post('/', function () use ($app) {
    /** @var Symfony\Component\HttpFoundation\Request $request */
    $request = $app['request'];
    $recipient = $request->get('recipient');
    $action = $request->get('submit');

    $app['send_message.' . $action]($recipient, $app['mailgun.domain'], $app['mailgun.api_key']);

    return ucfirst($action . ' email sent');
});

$app['send_message.simple'] = $app->protect(function (
    $recipient,
    $mailgunDomain,
    $mailgunApiKey
) {
    // Instantiate the client.
    $httpClient = new Http\Adapter\Guzzle6\Client();
    $mailgunClient = new Mailgun\Mailgun($mailgunApiKey, $httpClient);

    // Make the call to the client.
    $result = $mailgunClient->sendMessage($mailgunDomain, array(
        'from' => sprintf('Example Sender <mailgun@%s>', $mailgunDomain),
        'to' => $recipient,
        'subject' => 'Hello',
        'text' => 'Testing some Mailgun awesomeness!',
    ));
    return $result;
});

$app['send_message.complex'] = $app->protect(function (
    $recipient,
    $mailgunDomain,
    $mailgunApiKey,
    $cc = '',
    $bcc = ''
) {
    // Instantiate the client.
    $httpClient = new Http\Adapter\Guzzle6\Client();
    $mailgunClient = new Mailgun\Mailgun($mailgunApiKey, $httpClient);
    $fileAttachment = __DIR__ . '/attachment.txt';

    $postData = array(
        'from' => sprintf('Example Sender <mailgun@%s>', $mailgunDomain),
        'to' => $recipient,
        'subject' => 'Hello',
        'text' => 'Testing some Mailgun awesomeness!',
        'html' => '<html>HTML version of the body</html>',
    );

    if ($cc) {
        $postData['cc'] = $cc;
    }

    if ($bcc) {
        $postData['bcc'] = $bcc;
    }

    // Make the call to the client.
    $result = $mailgunClient->sendMessage($mailgunDomain, $postData, array(
        'attachment' => array($fileAttachment, $fileAttachment),
    ));
    return $result;
});

return $app;
