<?php
/**
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// create the Silex application
$app = new Application();

$app['sendgrid'] = function (Application $app) {
    return new SendGrid($app['sendgrid.api_key']);
};

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
    $mail = new SendGrid\Mail(
        new SendGrid\Email(null, $app['sendgrid.sender']),
        'This is a test email',
        new SendGrid\Email(null, $request->get('recipient')),
        new SendGrid\Content('text/plain', 'Example text body.')
    );
    /** @var SendGrid $sendgrid */
    $sendgrid = $app['sendgrid'];
    $response = $sendgrid->client->mail()->send()->post($mail);
    if ($response->statusCode() < 200 || $response->statusCode() >= 300) {
        return new Response($response->body(), $response->statusCode());
    }
    return 'Email sent.';
});

return $app;
