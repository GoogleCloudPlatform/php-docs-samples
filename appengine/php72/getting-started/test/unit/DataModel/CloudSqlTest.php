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
namespace Google\Cloud\Samples\Bookshelf\DataModel;

use Google\Cloud\Samples\Bookshelf\GetConfigTrait;
use Google\Cloud\TestUtils\TestTrait;
use PDO;

class CloudSqlTest extends \PHPUnit_Framework_TestCase
{
    use GetConfigTrait;
    use TestTrait;

    public function setUp()
    {
        $config = $this->getConfig();

        $mysql_dsn_local = sprintf('mysql:host=127.0.0.1;port=%s;dbname=%s',
            $config['mysql_port'],
            $config['mysql_database_name']);

        $pdo = new Pdo(
            $mysql_dsn_local,
            $config['mysql_user'],
            $config['mysql_password']
        );

        $this->model = new CloudSql($pdo);
    }

    public function testDataModel()
    {
        $model = $this->model;
        // Iterate over the existing books and count the rows.
        $fetch = array('cursor' => null);
        $rowCount = 0;
        do {
            $fetch = $model->listBooks(10, $fetch['cursor']);
            $rowCount += count($fetch['books']);
        } while ($fetch['cursor']);

        // Insert two books.
        $breakfastId = $model->create(array(
            'title' => 'Breakfast of Champions',
            'author' => 'Kurt Vonnegut',
            'published_date' => 'April 20th, 2016'

        ));

        $bellId = $model->create(array(
            'title' => 'For Whom the Bell Tolls',
            'author' => 'Ernest Hemingway'
        ));

        // Try to create a book with a bad property name.
        try {
            $model->create(array(
                'bogus' => 'Teach your owl to drive!'
            ));
            $this->fail('Should have thrown exception');
        } catch (\Exception $e) {
            // Good.  An exception is expected.
        }

        // account for eventual consistencty
        $retries = 0;
        $maxRetries = 10;
        do {
            $result = $model->listBooks($rowCount + 2);
            $newCount = count($result['books']);
            $retries++;
            if ($newCount < $rowCount + 2) {
                sleep(2 ** $retries);
            }
        } while ($newCount < $rowCount + 2 && $retries < $maxRetries);
        $this->assertEquals($rowCount + 2, $newCount);

        // Iterate over the books again
        do {
            // Only fetch one book at a time to test that code path.
            $fetch = $model->listBooks(1, $fetch['cursor']);
            // Check if id is correctly set.
            if (count($fetch['books']) > 0) {
                $this->assertNotNull($fetch['books'][0]['id']);
            }
        } while ($fetch['cursor']);

        // Make sure the book we read looks like the book we wrote.
        $breakfastBook = $model->read($breakfastId);
        $this->assertEquals('Breakfast of Champions', $breakfastBook['title']);
        $this->assertEquals('Kurt Vonnegut', $breakfastBook['author']);
        $this->assertEquals($breakfastId, $breakfastBook['id']);
        $this->assertFalse(isset($breakfastBook['description']));
        $this->assertEquals('April 20th, 2016', $breakfastBook['published_date']);

        // Try updating a book.
        $breakfastBook['description'] = 'A really fun read.';
        $breakfastBook['published_date'] = 'April 21st, 2016';
        $model->update($breakfastBook);
        $breakfastBookCopy = $model->read($breakfastId);

        // And confirm it was correctly updated.
        $this->assertEquals(
            'A really fun read.',
            $breakfastBookCopy['description']
        );
        $this->assertEquals('April 21st, 2016', $breakfastBookCopy['published_date']);

        // Update it again and delete the description.
        $breakfastBook['description'] = '';
        $breakfastBook['author'] = '';
        $model->update($breakfastBook);
        $breakfastBookCopy = $model->read($breakfastId);
        // And confirm it was correctly updated.
        $this->assertEquals('', $breakfastBookCopy['description']);
        $this->assertEquals('', $breakfastBookCopy['author']);

        // Try updating the book with a bad property name.
        try {
            $book['bogus'] = 'The power of scratching.';
            $model->update($book);
            $this->fail('Should have thrown exception');
        } catch (\Exception $e) {
            // Good.  An exception is expected.
        }

        // Clean up.
        $result = $model->delete($breakfastId);
        $this->assertTrue((bool)$result);
        $this->assertFalse($model->read($breakfastId));
        $this->assertTrue((bool)$model->read($bellId));
        $result = $model->delete($bellId);
        $this->assertTrue((bool)$result);
        $this->assertFalse($model->read($bellId));
    }
}
