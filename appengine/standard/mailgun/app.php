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

require_once __DIR__ . '/functions.php';

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    if ($app['mailgun.domain'] == 'MAILGUN_DOMAIN_NAME') {
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
    $sendFunction = sprintf('send%sMessage', ucfirst($action));

    $sendFunction($recipient, $app['mailgun.domain'], $app['mailgun.api_key']);

    return ucfirst($action . ' email sent');
});

return $app;
