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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Google\Cloud\Samples\Bookshelf\DataModel\CloudSql;
use Google\Cloud\Storage\Bucket;

$app->get('/', function (Request $request) use ($app) {
    return $app->redirect('/books/');
});

// [START index]
$app->get('/books/', function (Request $request) use ($app) {
    /** @var CloudSql $cloudsql */
    $cloudsql = $app['cloud_sql'];
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    $token = $request->query->get('page_token');
    $bookList = $cloudsql->listBooks($app['bookshelf.page_size'], $token);

    return $twig->render('list.html.twig', array(
        'books' => $bookList['books'],
        'next_page_token' => $bookList['cursor'],
    ));
});
// [END index]

// [START add]
$app->get('/books/add', function () use ($app) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('form.html.twig', array(
        'action' => 'Add',
        'book' => array(),
    ));
});

$app->post('/books/add', function (Request $request) use ($app) {
    /** @var CloudSql $cloudsql */
    $cloudsql = $app['cloud_sql'];
    /** @var Bucket $bucket */
    $bucket = $app['cloud_storage_bucket'];
    $files = $request->files;
    $book = $request->request->all();
    $image = $files->get('image');
    if ($image && $image->isValid()) {
        // Store the
        $f = fopen($image->getRealPath(), 'r');
        $object = $bucket->upload($f, [
            'metadata' => ['contentType' => $image->getMimeType()],
            'predefinedAcl' => 'publicRead',
        ]);
        $book['image_url'] = $object->info()['mediaLink'];
    }
    $id = $cloudsql->create($book);

    return $app->redirect("/books/$id");
});
// [END add]

// [START show]
$app->get('/books/{id}', function ($id) use ($app) {
    /** @var CloudSql $cloudsql */
    $cloudsql = $app['cloud_sql'];
    $book = $cloudsql->read($id);
    if (!$book) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('view.html.twig', array('book' => $book));
});
// [END show]

// [START edit]
$app->get('/books/{id}/edit', function ($id) use ($app) {
    /** @var CloudSql $cloudsql */
    $cloudsql = $app['cloud_sql'];
    $book = $cloudsql->read($id);
    if (!$book) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('form.html.twig', array(
        'action' => 'Edit',
        'book' => $book,
    ));
});

$app->post('/books/{id}/edit', function (Request $request, $id) use ($app) {
    $book = $request->request->all();
    $book['id'] = $id;
    /** @var Bucket $bucket */
    $bucket = $app['cloud_storage_bucket'];
    /** @var CloudSql $cloudsql */
    $cloudsql = $app['cloud_sql'];
    if (!$cloudsql->read($id)) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    // [START add_image]
    $files = $request->files;
    $image = $files->get('image');
    if ($image && $image->isValid()) {
        $book['image_url'] = $bucket->storeFile(
            $image->getRealPath(),
            $image->getMimeType()
        );
    }
    // [END add_image]
    if ($cloudsql->update($book)) {
        return $app->redirect("/books/$id");
    }

    return new Response('Could not update book');
});
// [END edit]

// [START delete]
$app->post('/books/{id}/delete', function ($id) use ($app) {
    /** @var CloudSql $cloudsql */
    $cloudsql = $app['cloud_sql'];
    $book = $cloudsql->read($id);
    if ($book) {
        $cloudsql->delete($id);
        // [START delete_image]
        if (!empty($book['image_url'])) {
            /** @var Bucket $bucket */
            $bucket = $app['cloud_storage_bucket'];
            // get bucket name from image
            $name = parse_url(basename($book['image_url']), PHP_URL_PATH);
            $object = $bucket->object($name);
            $object->delete();
        }
        // [END delete_image]
        return $app->redirect('/books/', Response::HTTP_SEE_OTHER);
    }

    return new Response('', Response::HTTP_NOT_FOUND);
});
// [END delete]
