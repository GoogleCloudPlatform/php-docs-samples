Sending email with Mailgun
=================================

Overview
--------

The Mailgun API is built on HTTP. Our API is RESTful_ and it:

- Uses predictable, resource-oriented URLs.
- Uses built-in HTTP capabilities for passing parameters and authentication.
- Responds with standard HTTP response codes to indicate errors.
- Returns JSON_.

Mailgun has published libraries for various languages. You may use our
libraries, or your favorite/suggested HTTP/REST library available for your
programming language, to make HTTP calls to Mailgun.

Code samples are available for several programming languages in our
documentation_. Below are the language-specific notes we feel are useful.

Sign Up
-------

`Create a new Mailgun account`_ and as a Google Compute user your first 35,000
messages are free every month. Check out the monthly pricing calculator on the
sign up page for pricing on additional messages and volume discounts.

PHP
---

Our mailgun-php_ library is robust and provides an excellent interface to easily
interact with our API. To install the library, you will need to be using
Composer in your project. If you aren’t using Composer yet, it’s really simple!
Here’s how to install composer and the Mailgun library::

    # Install Composer
    curl -sS https://getcomposer.org/installer | php

    # Add Mailgun as a dependency (x.x is the SDK version)
    php composer.phar require mailgun/mailgun-php:~x.x

Next, just include Composer’s autoloader in your application to automatically
load the Mailgun library in your project::

    require 'vendor/autoload.php';
    use Mailgun\Mailgun;

Examples
--------

Sending a plain text message::

    # Include the Autoloader (see above for install instructions)
    require 'vendor/autoload.php';
    use Mailgun\Mailgun;

    # Instantiate the client.
    $mgClient = new Mailgun('YOUR_API_KEY');
    $domain = "YOUR_DOMAIN_NAME";

    # Make the call to the client.
    $result = $mgClient->sendMessage($domain, array(
        'from'    => 'Excited User <mailgun@YOUR_DOMAIN_NAME>',
        'to'      => 'Baz <YOU@YOUR_DOMAIN_NAME>',
        'subject' => 'Hello',
        'text'    => 'Testing some Mailgun awesomness!'
    ));

Sending a message with HTML and text parts. This example also attaches two
files to the message::

    # Include the Autoloader (see above for install instructions)
    require 'vendor/autoload.php';
    use Mailgun\Mailgun;

    # Instantiate the client.
    $mgClient = new Mailgun('YOUR_API_KEY');
    $domain = "YOUR_DOMAIN_NAME";

    # Make the call to the client.
    $result = $mgClient->sendMessage($domain, array(
        'from'    => 'Excited User <YOU@YOUR_DOMAIN_NAME>',
        'to'      => 'foo@example.com',
        'cc'      => 'baz@example.com',
        'bcc'     => 'bar@example.com',
        'subject' => 'Hello',
        'text'    => 'Testing some Mailgun awesomness!',
        'html'    => '<html>HTML version of the body</html>'
    ), array(
        'attachment' => array('/path/to/file.txt', '/path/to/file.txt')
    ));

Sample response::

    {
        "message": "Queued. Thank you.",
        "id": "<20111114174239.25659.5817@samples.mailgun.org>"
    }

For more detailed examples and information about many other topics including
tracking and routing messages please see our online documentation_.

.. _RESTful: http://en.wikipedia.org/wiki/Representational_State_Transfer
.. _JSON: http://en.wikipedia.org/wiki/JSON
.. _documentation: https://documentation.mailgun.com
.. _Create a new Mailgun account: https://mailgun.com/signup
.. _mailgun-php: https://github.com/mailgun/mailgun-php

