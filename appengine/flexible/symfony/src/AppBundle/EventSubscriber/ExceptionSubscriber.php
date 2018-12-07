<?php

# [START error_reporting_setup_php_symfony]
// src/AppBundle/EventSubscriber/ExceptionSubscriber.php
namespace AppBundle\EventSubscriber;

use Google\Cloud\ErrorReporting\Bootstrap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [KernelEvents::EXCEPTION => [
            ['logException', 0]
        ]];
    }

    public function logException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        Bootstrap::exceptionHandler($exception);
    }
}
# [END error_reporting_setup_php_symfony]
