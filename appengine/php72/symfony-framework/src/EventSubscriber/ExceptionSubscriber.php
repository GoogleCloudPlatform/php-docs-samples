<?php

/**
 * Copyright 2019 Google Inc.
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

# [START error_reporting_setup_php_symfony]
// src/EventSubscriber/ExceptionSubscriber.php
namespace App\EventSubscriber;

use Google\Cloud\ErrorReporting\Bootstrap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * EventSubscrber used to log all exceptions to Stackdriver Error Reporting on Google Cloud Platform
 *
 * @author Brent Shaffer <bshafs@gmail.com>
 */
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
        Bootstrap::init();
        Bootstrap::exceptionHandler($exception);
    }
}
# [END error_reporting_setup_php_symfony]
