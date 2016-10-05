<?php
/**
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

namespace Google\Cloud\Samples\Datastore;

class quickstartTest extends \PHPUnit_Framework_TestCase
{
    public function testQuickstart()
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('GOOGLE_APPLICATION_CREDENTIALS must be set.');
        }

        $file = __DIR__ . '/../quickstart.php';

        // Invoke quickstart.php
        $entity = include $file;

        // Make sure it looks correct
        $this->assertInstanceOf('Google\Cloud\Datastore\Entity', $entity);
        $this->assertEquals('Bob', $entity->key()->path()[0]['name']);
    }
}
