<?php

use Mailgun\Mailgun;

# [START simple_message]
function sendSimpleMessage($recipient, $mailgunDomain, $mailgunApiKey)
{
    // Instantiate the client.
    $httpClient = new Http\Adapter\Guzzle6\Client();
    $mailgunClient = new Mailgun($mailgunApiKey, $httpClient);

    // Make the call to the client.
    $result = $mailgunClient->sendMessage($mailgunDomain, array(
        'from'    => sprintf('Example Sender <mailgun@%s>', $mailgunDomain),
        'to'      => $recipient,
        'subject' => 'Hello',
        'text'    => 'Testing some Mailgun awesomeness!',
    ));
}
# [END simple_message]

# [START complex_message]
function sendComplexMessage($recipient, $mailgunDomain, $mailgunApiKey, $cc = 'cc@example.com', $bcc = 'bcc@example.com')
{
    // Instantiate the client.
    $httpClient = new Http\Adapter\Guzzle6\Client();
    $mailgunClient = new Mailgun($mailgunApiKey, $httpClient);
    $fileAttachment = __DIR__ . '/attachment.txt';

    // Make the call to the client.
    $result = $mailgunClient->sendMessage($mailgunDomain, array(
        'from'    => sprintf('Example Sender <mailgun@%s>', $mailgunDomain),
        'to'      => $recipient,
        'cc'      => $cc,
        'bcc'     => $bcc,
        'subject' => 'Hello',
        'text'    => 'Testing some Mailgun awesomeness!',
        'html'    => '<html>HTML version of the body</html>'
    ), array(
        'attachment' => array($fileAttachment, $fileAttachment)
    ));
}
# [END complex_message]
