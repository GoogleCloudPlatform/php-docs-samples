<?php

use Silex\Application;

require_once __DIR__ . '/functions.php';

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    if ($app['mailgun.domain'] == 'YOUR_DOMAIN_NAME') {
        return 'set your mailgun domain and API key in <code>web/index.php</code>';
    }
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

$app->post('/', function () use ($app) {
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

return $app;
