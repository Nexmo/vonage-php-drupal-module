# Vonage Client Module for Drupal
[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg)](CODE_OF_CONDUCT.md)
[![Apache 2.0 licensed](https://img.shields.io/badge/license-Apache%202.0-blue.svg)](./LICENSE.txt)

<img src="https://developer.nexmo.com/assets/images/Vonage_Nexmo.svg" height="48px" alt="Nexmo is now known as Vonage" />

This is the Vonage API PHP client module for use with the Drupal CMS.
To use this, you'll need a Vonage account. Sign up [for free at nexmo.com][signup].

**This bundle is currently in development/beta status, so there may be bugs**

 * [Installation](#installation)
 * [Usage](#usage)
 * [Contributing](#contributing) 

## Installation

### Step 1: Add the module to your composer.json

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this module:

```console
$ composer require vonage/vonage_drupal
```

### Step 2: Install the Module

The module will automatically be detected by Drupal, and can be enabled by
logging in as a user with permissions to enable modules. Go to the "Extend"
page and either search for "Vonage", or scroll down to the "Communications"
section. Check the box next to "Vonage API SDK", and click the "Install"
button.

### Step 3: Configuration

You can configure the bundle with your application details by goint to the 
"Configuration" page and click on "Vonage API Settings" under the "System"
section.

You can then fill in the needed credentials from your [Vonage Dashboard][dashboard].
The module allows you to enter your Vonage API key and secret, and if you are
using Application-based APIs like the Voice API you can enter an Application ID
as well as a private key to use.

Once you have entered either set of credentials, you can test basic
functionlity using the "Vonage SMS API Testing" or "Vonage Voice API Testing"
tabs. These allow you to send a test SMS and test voice call.

After you have entered your credentials, you can use any of the APIs that
are available in our PHP SDK. 

## Usage

### Calling the Vonage APIs

This bundle takes care of all the client creation needed for making the Vonage
client, and adds it to the service container. You can pull the class from the
service container or use it as a service in other service declarations.

```php
namespace Drupal\my_module\Controller;

use Vonage\Client;
use Vonage\SMS\Message\SMS;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MyController extends ControllerBase
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public static function create(ContainerInterface $container)
    {
        return new static($container->get(Client::class));
    }

    public function controllerAction(): array
    {
        $this->client->sms()->send(
            new SMS($to, from, $message)
        );

        $build = [
            '#markup' => 'Hello World!',
        ];

        return $build;
    }
}
```

### Working with Incoming Webhooks

Many of the Vonage APIs, especially the SMS and Voice API, work with your
application through a concept of a Webhook. This is where the Vonage API
servers make a request to YOUR application, instead of your application
making a request to Vonage.

The Vonage API provides a way to interpret the incoming web request, and will
generate an appropriate object. All you need to do is know which kind of
request, either SMS or Voice, is coming to a specific route.

#### Incoming SMS Messages

When you elect to have a number accept SMS, Vonage will ask for the URL to send
the information to. You can create a controller in your module with a route
that can accept the incoming request, and use the `\Vonage\SMS\Webhook\Factory`
to convert that request into an object.

```php
namespace Drupal\my_module\Controller;

use Vonage\SMS\Webhook\Factory;
use Vonage\SMS\Webhook\InboundSMS;
use Drupal\Core\Controller\ControllerBase;

class MyController extends ControllerBase
{
    public function incomingSMS(): array
    {
        /** @var InboundSMS $inboundSMS */
        $inboundSMS = Factory::createFromGlobals();

        $to = $inboundSMS->getTo();
        $from = $inboundSMS->getFrom();
        $text = $inboundSMS->getText();

        // ...
    }
}
```

#### Incoming Voice Calls

If you are building an interactive voice application, you can set up an Answer
Webhook in the Application Settings on the Vonage Dashboard. You can create a
controller in your module with a route that can accept the incoming call and
use the `\Vonage\Voice\Webhook\Factory` to convert that request into an object.

```php
namespace Drupal\my_module\Controller;

use Vonage\Voice\Webhook\Factory;
use Vonage\Voice\Webhook\Answer;
use Drupal\Core\Controller\ControllerBase;

class MyController extends ControllerBase
{
    public function answerCall(): array
    {
        /** @var Answer $inboundCall */
        $inboundCall = Factory::createFromGlobals();

        $to = $inboundCall->getTo();
        $from = $inboundCall->getFrom();
        $uuid = $inboundCall->getUuid();

        // ...
    }
}
```

#### Incoming Voice Events

In addition to being able to answer a call, the Voice API is heavily event
driven. You can set up an Event Webhook in the Application Settings on the
Vonage Dashboard in addition to the Answer webhook. You can create a
controller in your module with a route that can accept the events and use the
`\Vonage\Voice\Webhook\Factory` to convert that request into an object.

When working with the objects, you will want to inspect the type of object
that is generated as there are many types of events with their own object
structure.

You can see all the available event types at [https://github.com/Vonage/vonage-php-sdk-core/tree/master/src/Voice/Webhook](https://github.com/Vonage/vonage-php-sdk-core/tree/master/src/Voice/Webhook).

```php
namespace Drupal\my_module\Controller;

use Vonage\Voice\Webhook\Factory;
use Vonage\Voice\Webhook\Answer;
use Vonage\Voice\Webhook\Event;
use Drupal\Core\Controller\ControllerBase;

class MyController extends ControllerBase
{
    public function voiceEventHandler(): array
    {
        $event = Factory::createFromGlobals();

        if ($event instanceof Event) {
            // ...
        } elseif ($event instanceof Notification) {
            // ...
        }

        // ...
    }
}
```

## Contributing

This library is actively developed, and we love to hear from you! Please feel free to [create an issue][issues] or [open a pull request][pulls] with your questions, comments, suggestions and feedback.

[signup]: https://dashboard.nexmo.com/sign-up?utm_source=DEV_REL&utm_medium=github&utm_campaign=php-drupal-module
[issues]: https://github.com/nexmo/vonage-php-drupal-module/issues
[pulls]: https://github.com/nexmo/vonage-php-drupal-module/pulls
