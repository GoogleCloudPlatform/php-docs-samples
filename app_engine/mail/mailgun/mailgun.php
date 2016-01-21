<?php

/*
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
use Mailgun\Mailgun;

// Install composer dependencies with "composer install"
// @see http://getcomposer.org for more information.
require __DIR__ . '/vendor/autoload.php';

// create the Silex application
$app = new Application();

// set your Mailgun domain name and API key
$app['mailgun.domain'] = 'YOUR_DOMAIN_NAME';
$app['mailgun.api_key'] = 'YOUR_API_KEY';

$app->get('/', function() {
    return <<<EOF
<!doctype html>
<html><body>
<form method="POST">
<input type="text" name="recipient" placeholder="Enter recipient email">
<input type="submit" name="submit" value="Send simple email">
<input type="submit" name="submit" value="Send complex email">
</form>
</body></html>
EOF;
});

$app->post('/', function() use ($app) {
    /** @var Symfony\Component\HttpFoundation\Request $request */
    $request = $app['request'];
    $recipient = $request->get('recipient');
    $action = $request->get('submit');
    $actionFunction = ($action == 'Send simple email')
        ? 'sendSimpleMessage'
        : 'sendComplexMessage';

    $actionFunction($recipient, $app['mailgun.domain'], $app['mailgun.api_key']);

    return 'Mail sent';
});

# [START simple_message]
function sendSimpleMessage($recipient, $mailgunDomain, $mailgunApiKey) {
    // Instantiate the client.
    $mailgunClient = new Mailgun($mailgunApiKey);

    // Make the call to the client.
    $result = $mailgunClient->sendMessage($mailgunDomain, array(
        'from'    => sprintf('Example Sender <mailgun@%s>', $mailgunDomain),
        'to'      => 'foo@example.com',
        'subject' => 'Hello',
        'text'    => 'Testing some Mailgun awesomness!',
    ));
}
# [END simple_message]

# [START complex_message]
function sendComplexMessage($recipient, $mailgunDomain, $mailgunApiKey, $cc = null, $bcc = null) {
    // Instantiate the client.
    $mailgunClient = new Mailgun($mailgunApiKey);
    $fileAttachment = __DIR__ . '/attachment.txt';

    // Make the call to the client.
    $result = $mailgunClient->sendMessage($mailgunDomain, array(
        'from'    => sprintf('Example Sender <mailgun@%s>', $mailgunDomain),
        'to'      => 'foo@example.com',
        'cc'      => $cc,
        'bcc'     => $bcc,
        'subject' => 'Hello',
        'text'    => 'Testing some Mailgun awesomness!',
        'html'    => '<html>HTML version of the body</html>'
    ), array(
        'attachment' => array($fileAttachment, $fileAttachment)
    ));
}
# [END complex_message]

// Run the app!
// use "gcloud preview app deploy" or run "php -S localhost:8000"
// and browse to "mailgun.php"
$app->run();
