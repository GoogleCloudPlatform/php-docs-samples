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


namespace Google\Cloud\Samples\Vision;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for transcribe commands.
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    private $bucketName;

    public function setUp()
    {
        if (!$creds = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }
        $this->bucketName = getenv('GCS_BUCKET_NAME');
    }

    public function testLabelCommand()
    {
        $path = __DIR__ . '/data/cat.jpg';
        $output = $this->runCommand('label', $path);
        $this->assertContains('cat', $output);
        $this->assertContains('mammal', $output);
    }

    public function testLabelCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $path = 'gs://' . $this->bucketName . '/cat.jpg';
        $output = $this->runCommand('label', $path);
        $this->assertContains('cat', $output);
        $this->assertContains('mammal', $output);
    }

    public function testTextCommand()
    {
        $path = __DIR__ . '/data/sabertooth.gif';
        $output = $this->runCommand('text', $path);
        $this->assertContains('extinct', $output);
    }

    public function testTextCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $path = 'gs://' . $this->bucketName . '/sabertooth.gif';
        $output = $this->runCommand('text', $path);
        $this->assertContains('extinct', $output);
    }

    public function testTextCommandWithImageLackingText()
    {
        $path = __DIR__ . '/data/faulkner.jpg';
        $output = $this->runCommand('text', $path);
        $this->assertContains("Texts:\n", $output);
    }

    public function testFaceCommand()
    {
        $path = __DIR__ . '/data/face.png';
        $output = $this->runCommand('face', $path);
        $this->assertContains('Anger: ', $output);
        $this->assertContains('Joy: ', $output);
        $this->assertContains('Surprise: ', $output);
    }

    public function testFaceCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $path = 'gs://' . $this->bucketName . '/face.png';
        $output = $this->runCommand('face', $path);
        $this->assertContains('Anger: ', $output);
        $this->assertContains('Joy: ', $output);
        $this->assertContains('Surprise: ', $output);
    }

    public function testFaceCommandWithImageLackingFaces()
    {
        $path = __DIR__ . '/data/tower.jpg';
        $output = $this->runCommand('face', $path);
        $this->assertContains("Faces:\n", $output);
    }

    public function testFaceCommandWithOutput()
    {
        $path = __DIR__ . '/data/face.png';
        $output_file = sys_get_temp_dir() . '/face.png';
        $output = $this->runCommand('face', $path, $output_file);
        $this->assertContains('Anger: ', $output);
        $this->assertContains('Joy: ', $output);
        $this->assertContains('Surprise: ', $output);
        $this->assertContains('Output image written to ' . $output_file, $output);
        $this->assertTrue(file_exists($output_file));
    }

    public function testFaceCommandWithImageLackingFacesAndOutput()
    {
        $path = __DIR__ . '/data/tower.jpg';
        $output_file = sys_get_temp_dir() . '/tower.jpg';
        $output = $this->runCommand('face', $path, $output_file);
        $this->assertContains("Faces:\n", $output);
        $this->assertFalse(file_exists($output_file));
    }

    public function testLandmarkCommand()
    {
        $path = __DIR__ . '/data/tower.jpg';
        $output = $this->runCommand('landmark', $path);
        $this->assertContains('Eiffel', $output);
    }

    public function testLandmarkCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $path = 'gs://' . $this->bucketName . '/tower.jpg';
        $output = $this->runCommand('landmark', $path);
        $this->assertContains('Eiffel', $output);
    }

    public function testLandmarkCommandWithImageLackingLandmarks()
    {
        $path = __DIR__ . '/data/faulkner.jpg';
        $output = $this->runCommand('landmark', $path);
        $this->assertContains("Landmarks:\n", $output);
    }

    public function testLogoCommand()
    {
        $path = __DIR__ . '/data/logo.jpg';
        $output = $this->runCommand('logo', $path);
        $this->assertContains('Google', $output);
    }

    public function testLogoCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $path = 'gs://' . $this->bucketName . '/logo.jpg';
        $output = $this->runCommand('logo', $path);
        $this->assertContains('Google', $output);
    }

    public function testLogoCommandWithImageLackingLogo()
    {
        $path = __DIR__ . '/data/tower.jpg';
        $output = $this->runCommand('logo', $path);
        $this->assertContains("Logos:\n", $output);
    }

    public function testSafeSearchCommand()
    {
        $path = __DIR__ . '/data/logo.jpg';
        $output = $this->runCommand('safe-search', $path);
        $this->assertContains('Adult:', $output);
    }

    public function testSafeSearchCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $path = 'gs://' . $this->bucketName . '/logo.jpg';
        $output = $this->runCommand('safe-search', $path);
        $this->assertContains('Adult:', $output);
    }

    public function testImagePropertyCommand()
    {
        $path = __DIR__ . '/data/logo.jpg';
        $output = $this->runCommand('property', $path);
        $this->assertContains('red:', $output);
        $this->assertContains('green:', $output);
        $this->assertContains('blue:', $output);
    }

    public function testImagePropertyCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $path = 'gs://' . $this->bucketName . '/logo.jpg';
        $output = $this->runCommand('property', $path);
        $this->assertContains('red:', $output);
        $this->assertContains('green:', $output);
        $this->assertContains('blue:', $output);
    }

    # Tests for Vision 1.1 Features
    public function testCropHintsCommand()
    {
        $path = __DIR__ . '/data/wakeupcat.jpg';
        $output = $this->runCommand('crop-hints', $path);
        $this->assertContains('Crop Hints:', $output);
        $this->assertContains('X: 0 Y: 0', $output);
        $this->assertContains('X: 599 Y: 0', $output);
        $this->assertContains('X: 599 Y: 475', $output);
        $this->assertContains('X: 0 Y: 475', $output);
    }

    public function testCropHintsCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $path = 'gs://' . $this->bucketName . '/wakeupcat.jpg';
        $output = $this->runCommand('crop-hints', $path);
        $this->assertContains('Crop Hints:', $output);
        $this->assertContains('X: 0 Y: 0', $output);
        $this->assertContains('X: 599 Y: 0', $output);
        $this->assertContains('X: 599 Y: 475', $output);
        $this->assertContains('X: 0 Y: 475', $output);
    }

    public function testDocumentTextCommand()
    {
        $path = __DIR__ . '/data/text.jpg';
        $output = $this->runCommand('document-text', $path);
        $this->assertContains('Document text:', $output);
        $this->assertContains('the PS4 will automatically restart', $output);
        $this->assertContains('37%', $output);
        $this->assertContains('Block text:', $output);
        $this->assertContains('Block bounds:', $output);
    }

    public function testDocumentTextCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $path = 'gs://' . $this->bucketName . '/text.jpg';
        $output = $this->runCommand('document-text', $path);
        $this->assertContains('Document text:', $output);
        $this->assertContains('the PS4 will automatically restart', $output);
        $this->assertContains('37%', $output);
        $this->assertContains('Block text:', $output);
        $this->assertContains('Block bounds:', $output);
    }

    public function testDetectWebCommand()
    {
        $path = __DIR__ . '/data/landmark.jpg';
        $output = $this->runCommand('web', $path);
        $this->assertContains('Web Entities found:', $output);
        $this->assertContains('Palace of Fine Arts Theatre', $output);
        $this->assertContains('Pier 39', $output);
    }

    public function testDetectWebCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $path = 'gs://' . $this->bucketName . '/landmark.jpg';
        $output = $this->runCommand('web', $path);
        $this->assertContains('Web Entities found:', $output);
        $this->assertContains('Palace of Fine Arts Theatre', $output);
        $this->assertContains('Pier 39', $output);
    }

    private function runCommand($commandName, $path, $output=null)
    {
        $application = require __DIR__ . '/../vision.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute(
            [
                'path' => $path,
                'output' => $output
            ],
            ['interactive' => false]
        );
        return ob_get_clean();
    }

}
