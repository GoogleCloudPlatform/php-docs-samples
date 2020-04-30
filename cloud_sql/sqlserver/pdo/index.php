<?php
/*
 * Copyright 2020 Google LLC.
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

declare(strict_types=1);

use GuzzleHttp\Psr7;

include __DIR__ . '/vendor/autoload.php';

$app = include __DIR__ . '/src/app.php';

$app->get('/', function ($request, $response) {
    $this->get('votes')->createTableIfNotExists();

    return $this->get('view')->render($response, 'template.twig', [
        'votes' => $this->get('votes')->listVotes(),
        'tabCount' => $this->get('votes')->getCountByValue('TABS'),
        'spaceCount' => $this->get('votes')->getCountByValue('SPACES'),
    ]);
});

$app->post('/', function ($request, $response) {
    $this->get('votes')->createTableIfNotExists();

    $message = 'Invalid vote. Choose Between TABS and SPACES';

    $formData = $request->getParsedBody() + [
        'voteValue' => ''
    ];

    if (in_array($formData['voteValue'], ['SPACES', 'TABS'])) {
        $message = $this->get('votes')->insertVote($formData['voteValue'])
            ? 'Vote cast for ' . $formData['voteValue']
            : 'An error occurred';
    }

    return $response->withBody(Psr7\stream_for($message));
});

$app->run();
