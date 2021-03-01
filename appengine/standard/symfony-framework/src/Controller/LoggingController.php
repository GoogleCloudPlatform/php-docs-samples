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

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

/**
 * Controller used to test Stackdriver Logging integration on Google Cloud Platform
 *
 * @Route("/logging")
 *
 * @author Brent Shaffer <bshafs@gmail.com>
 */
class LoggingController extends AbstractController
{
    /**
     * @Route("/notice/{token}", defaults={"token"=0}, methods={"GET"})
     */
    public function notice(LoggerInterface $logger, $token): Response
    {
        $logger->notice("Hello my log, token: $token");

        return $this->render('default/homepage.html.twig');
    }

    /**
     * @Route("/exception/{token}", defaults={"token"=0}, methods={"GET"})
     */
    public function exception($token): Response
    {
        throw new Exception("Intentional exception, token: $token");
    }
}
