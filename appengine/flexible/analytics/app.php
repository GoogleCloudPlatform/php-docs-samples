<?php
/**
 * Copyright 2016 Google Inc.
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

use GuzzleHttp\Client;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__ ];

$app->get('/', function (Application $app, Request $request) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    $trackingId = $app['GA_TRACKING_ID'];
    # [START gae_flex_analytics_track_event]
    $baseUri = 'http://www.google-analytics.com/';
    $client = new GuzzleHttp\Client(['base_uri' => $baseUri]);
    $formData = [
        'v' => '1',  # API Version.
        'tid' => $trackingId,  # Tracking ID / Property ID.
        # Anonymous Client Identifier. Ideally, this should be a UUID that
        # is associated with particular user, device, or browser instance.
        'cid' => '555',
        't' => 'event',  # Event hit type.
        'ec' => 'Poker',  # Event category.
        'ea' => 'Royal Flush',  # Event action.
        'el' => 'Hearts',  # Event label.
        'ev' => 0,  # Event value, must be an integer
    ];
    $response = $client->request('POST', 'collect', ['form_params' => $formData]);
    # [END gae_flex_analytics_track_event]
    return $twig->render('index.html.twig', [
            'base_uri' => $baseUri,
            'response_code' => $response->getStatusCode(),
            'response_reason' => $response->getReasonPhrase()]);
});

return $app;
