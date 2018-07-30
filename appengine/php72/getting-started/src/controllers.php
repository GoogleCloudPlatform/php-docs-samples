<?php

/*
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Bookshelf;

/*
 * Adds all the controllers to $app.  Follows Silex Skeleton pattern.
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Google\Cloud\Storage\Bucket;

$app->get('/', function (Request $request, Response $response) {
    return $response->withRedirect('/books/');
})->setName('home');

// [START index]
$app->get('/books/', function (Request $request, Response $response) {
    $token = $request->getQueryParam('page_token');
    $bookList = $this->cloudsql->listBooks(10, $token);

    return $this->view->render($response, 'list.html.twig', [
        'books' => $bookList['books'],
        'next_page_token' => $bookList['cursor'],
    ]);
})->setName('books');
// [END index]

// [START add]
$app->get('/books/add', function (Request $request, Response $response) {
    return $this->view->render($response, 'form.html.twig', [
        'action' => 'Add',
        'book' => array(),
    ]);
});

$app->post('/books/add', function (Request $request, Response $response) {
    $book = $request->getParsedBody();
    $files = $request->getUploadedFiles();
    if ($files['image']->getSize()) {
        // Store the
        $image = $files['image'];
        $object = $this->bucket->upload($image->getStream(), [
            'metadata' => ['contentType' => $image->getClientMediaType()],
            'predefinedAcl' => 'publicRead',
        ]);
        $book['image_url'] = $object->info()['mediaLink'];
    }
    $id = $this->cloudsql->create($book);

    return $response->withRedirect("/books/$id");
});
// [END add]

// [START show]
$app->get('/books/{id}', function (Request $request, Response $response, $args) {
    $book = $this->cloudsql->read($args['id']);
    if (!$book) {
        return $response->withStatus(404);
    }
    return $this->view->render($response, 'view.html.twig', ['book' => $book]);
});
// [END show]

// [START edit]
$app->get('/books/{id}/edit', function (Request $request, Response $response, $args) {
    $book = $this->cloudsql->read($args['id']);
    if (!$book) {
        return $response->withStatus(404);
    }

    return $this->view->render($response, 'form.html.twig', [
        'action' => 'Edit',
        'book' => $book,
    ]);
});

$app->post('/books/{id}/edit', function (Request $request, Response $response, $args) {
    if (!$this->cloudsql->read($args['id'])) {
        return $response->withStatus(404);
    }
    $book = $request->getParsedBody();
    $book['id'] = $args['id'];
    // [START add_image]
    $files = $request->getUploadedFiles();
    if ($files['image']->getSize()) {
        $image = $files['image'];
        $object = $this->bucket->upload($image->getStream(), [
            'metadata' => ['contentType' => $image->getClientMediaType()],
            'predefinedAcl' => 'publicRead',
        ]);
        $book['image_url'] = $object->info()['mediaLink'];
    }
    // [END add_image]
    if ($this->cloudsql->update($book)) {
        return $response->withRedirect("/books/$args[id]");
    }

    return new Response('Could not update book');
});
// [END edit]

// [START delete]
$app->post('/books/{id}/delete', function (Request $request, Response $response, $args) {;
    $book = $this->cloudsql->read($args['id']);
    if ($book) {
        $this->cloudsql->delete($args['id']);
        // [START delete_image]
        if (!empty($book['image_url'])) {
            // get bucket name from image
            $name = parse_url(basename($book['image_url']), PHP_URL_PATH);
            $object = $this->bucket->object($name);
            $object->delete();
        }
        // [END delete_image]
        return $response->withRedirect('/books/');
    }

    return $response->withStatus(404);
});
// [END delete]
