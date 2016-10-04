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
class quickstartTest extends PHPUnit_Framework_TestCase
{
    private $dataset;

    public function testQuickstart()
    {
        $datasetId = 'my_new_dataset_' . time();
        $file = sys_get_temp_dir() . '/quickstart_quickstart.php';
        $contents = file_get_contents(__DIR__ . '/../quickstart.php');
        $contents = str_replace(
            ['my_new_dataset', '__DIR__'],
            [$datasetId, sprintf('"%s/.."', __DIR__)],
            $contents
        );
        file_put_contents($file, $contents);

        // Invoke quickstart.php
        $this->dataset = include $file;

        // Make sure it looks correct
        $this->assertInstanceOf('Google\Cloud\BigQuery\Dataset', $this->dataset);
        $this->assertEquals($datasetId, $this->dataset->id());
    }

    public function tearDown()
    {
        if ($this->dataset) {
            $this->dataset->delete();
        }
    }
}
