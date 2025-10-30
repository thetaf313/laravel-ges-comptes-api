<?php

// Include the Autoloader (see "Libraries" for install instructions)
require 'vendor/autoload.php';

// Use the Mailgun class from mailgun/mailgun-php v4.2
use Mailgun\Mailgun;

// Instantiate the client.
$mg = Mailgun::create('3b93aa8a6d2ca4eac10e3eebe5d76003-653fadca-5b17eeb3');
// When you have an EU-domain, you must specify the endpoint:
// $mg = Mailgun::create(getenv('API_KEY') ?: 'API_KEY', 'https://api.eu.mailgun.net');

// Compose and send your message.
$result = $mg->messages()->send(
    'sandbox95e62c35c1a845b587815ce757abedcc.mailgun.org',
    [
        'from' => 'Mailgun Sandbox <postmaster@sandbox95e62c35c1a845b587815ce757abedcc.mailgun.org>',
        'to' => 'Moustapha Sayande <moustiques1234@gmail.com>',
        'subject' => 'Hello Moustapha Sayande',
        'text' => 'Congratulations Moustapha Sayande, you just sent an email with Mailgun! You are truly awesome!'
    ]
);

print_r($result->getMessage());
