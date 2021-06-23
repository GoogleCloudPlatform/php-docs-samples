<?php

/*
 * Copyright 2018 Google LLC All Rights Reserved.
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
 * Adds all the controllers to Slim PHP $app.
 */
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Google\Cloud\Storage\Bucket;

$container = $app->getContainer();

$app->get('/', function (Request $request, Response $response) use ($container) {
    return $response
        ->withHeader('Location', '/books')
        ->withStatus(302);
})->setName('home');

$app->get('/books', function (Request $request, Response $response) use ($container) {
    $token = (int) $request->getUri()->getQuery('page_token');
    $bookList = $container->get('cloudsql')->listBooks(10, $token);

    return $container->get('view')->render($response, 'list.html.twig', [
        'books' => $bookList['books'],
        'next_page_token' => $bookList['cursor'],
    ]);
})->setName('books');

$app->get('/books/add', function (Request $request, Response $response) use ($container) {
    return $container->get('view')->render($response, 'form.html.twig', [
        'action' => 'Add',
        'book' => array(),
    ]);
});

$app->post('/books/add', function (Request $request, Response $response) use ($container) {
    $book = $request->getParsedBody();
    $files = $request->getUploadedFiles();
    if ($files['image']->getSize()) {
        // Store the uploaded files in a Cloud Storage object.
        $image = $files['image'];
        $object = $container->get('bucket')->upload($image->getStream(), [
            'metadata' => ['contentType' => $image->getClientMediaType()],
            'predefinedAcl' => 'publicRead',
        ]);
        $book['image_url'] = $object->info()['mediaLink'];
    }
    $id = $container->get('cloudsql')->create($book);

    return $response
        ->withHeader('Location', "/books/$id")
        ->withStatus(302);
});

$app->get('/books/{id}', function (Request $request, Response $response, $args) use ($container) {
    $book = $container->get('cloudsql')->read($args['id']);
    if (!$book) {
        return $response->withStatus(404);
    }
    return $container->get('view')->render($response, 'view.html.twig', ['book' => $book]);
});

$app->get('/books/{id}/edit', function (Request $request, Response $response, $args) use ($container) {
    $book = $container->get('cloudsql')->read($args['id']);
    if (!$book) {
        return $response->withStatus(404);
    }

    return $container->get('view')->render($response, 'form.html.twig', [
        'action' => 'Edit',
        'book' => $book,
    ]);
});

$app->post('/books/{id}/edit', function (Request $request, Response $response, $args) use ($container) {
    if (!$container->get('cloudsql')->read($args['id'])) {
        return $response->withStatus(404);
    }
    $book = $request->getParsedBody();
    $book['id'] = $args['id'];
    $files = $request->getUploadedFiles();
    if ($files['image']->getSize()) {
        $image = $files['image'];
        $bucket = $container->get('bucket');
        $imageStream = $image->getStream();
        $imageContentType = $image->getClientMediaType();
        // [START gae_php_app_upload_image]
        // Set your own image file path and content type below to upload an
        // image to Cloud Storage.
        // $imageStream = fopen('/path/to/your_image.jpg', 'r');
        // $imageContentType = 'image/jpg';
        $object = $bucket->upload($imageStream, [
            'metadata' => ['contentType' => $imageContentType],
            'predefinedAcl' => 'publicRead',
        ]);
        $imageUrl = $object->info()['mediaLink'];
        // [END gae_php_app_upload_image]
        $book['image_url'] = $imageUrl;
    }
    if ($container->get('cloudsql')->update($book)) {
        return $response
            ->withHeader('Location', "/books/$args[id]")
            ->withStatus(302);
    }

    $response->getBody()->write('Could not update book');
    return $response;
});

$app->post('/books/{id}/delete', function (Request $request, Response $response, $args) use ($container) {
    $book = $container->get('cloudsql')->read($args['id']);
    if ($book) {
        $container->get('cloudsql')->delete($args['id']);
        if (!empty($book['image_url'])) {
            $objectName = parse_url(basename($book['image_url']), PHP_URL_PATH);
            $bucket = $container->get('bucket');
            // get bucket name from image
            // [START gae_php_app_delete_image]
            $object = $bucket->object($objectName);
            $object->delete();
            // [END gae_php_app_delete_image]
        }
        return $response
            ->withHeader('Location', '/books')
            ->withStatus(302);
    }

    return $response->withStatus(404);
});
