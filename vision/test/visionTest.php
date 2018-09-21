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

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for vision commands.
 */
class visionTest extends \PHPUnit_Framework_TestCase
{
    private $bucketName;

    public function setUp()
    {
        if (!$creds = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }
        $this->bucketName = getenv('GOOGLE_STORAGE_BUCKET');
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
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/cat.jpg';
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
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/sabertooth.gif';
        $output = $this->runCommand('text', $path);
        $this->assertContains('extinct', $output);
    }

    public function testTextCommandWithImageLackingText()
    {
        $path = __DIR__ . '/data/faulkner.jpg';
        $output = $this->runCommand('text', $path);
        $this->assertContains('0 texts found', $output);
    }

    public function testTextCommandWithImageLackingTextGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/faulkner.jpg';
        $output = $this->runCommand('text', $path);
        $this->assertContains('0 texts found', $output);
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
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/face.png';
        $output = $this->runCommand('face', $path);
        $this->assertContains('Anger: ', $output);
        $this->assertContains('Joy: ', $output);
        $this->assertContains('Surprise: ', $output);
    }

    public function testFaceCommandWithImageLackingFaces()
    {
        $path = __DIR__ . '/data/tower.jpg';
        $output = $this->runCommand('face', $path);
        $this->assertContains('0 faces found', $output);
    }

    public function testFaceCommandWithImageLackingFacesGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/tower.jpg';
        $output = $this->runCommand('face', $path);
        $this->assertContains('0 faces found', $output);
    }

    public function testLandmarkCommand()
    {
        $path = __DIR__ . '/data/tower.jpg';
        $output = $this->runCommand('landmark', $path);
        $this->assertContains('Eiffel', $output);
    }

    public function testLandmarkCommandGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/tower.jpg';
        $output = $this->runCommand('landmark', $path);
        $this->assertContains('Eiffel', $output);
    }

    public function testLandmarkCommandWithImageLackingLandmarks()
    {
        $path = __DIR__ . '/data/faulkner.jpg';
        $output = $this->runCommand('landmark', $path);
        $this->assertContains('0 landmark found', $output);
    }

    public function testLandmarkCommandWithImageLackingLandmarksGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/faulkner.jpg';
        $output = $this->runCommand('landmark', $path);
        $this->assertContains('0 landmark found', $output);
    }

    public function testLogoCommand()
    {
        $path = __DIR__ . '/data/logo.jpg';
        $output = $this->runCommand('logo', $path);
        $this->assertContains('Google', $output);
    }

    public function testLogoCommandGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/logo.jpg';
        $output = $this->runCommand('logo', $path);
        $this->assertContains('Google', $output);
    }

    public function testLocalizeObjectCommand()
    {
        $path = __DIR__ . '/data/puppies.jpg';
        $output = $this->runCommand('localize-object', $path);
        $this->assertContains('Dog', $output);
    }

    public function testLocalizeObjectCommandGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/puppies.jpg';
        $output = $this->runCommand('localize-object', $path);
        $this->assertContains('Dog', $output);
    }

    public function testLogoCommandWithImageLackingLogo()
    {
        $path = __DIR__ . '/data/tower.jpg';
        $output = $this->runCommand('logo', $path);
        $this->assertContains('0 logos found', $output);
    }

    public function testLogoCommandWithImageLackingLogoGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/tower.jpg';
        $output = $this->runCommand('logo', $path);
        $this->assertContains('0 logos found', $output);
    }

    public function testSafeSearchCommand()
    {
        $path = __DIR__ . '/data/logo.jpg';
        $output = $this->runCommand('safe-search', $path);
        $this->assertContains('Adult:', $output);
        $this->assertContains('Racy:', $output);
    }

    public function testSafeSearchCommandGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/logo.jpg';
        $output = $this->runCommand('safe-search', $path);
        $this->assertContains('Adult:', $output);
        $this->assertContains('Racy:', $output);
    }

    public function testImagePropertyCommand()
    {
        $path = __DIR__ . '/data/logo.jpg';
        $output = $this->runCommand('property', $path);
        $this->assertContains('Red:', $output);
        $this->assertContains('Green:', $output);
        $this->assertContains('Blue:', $output);
    }

    public function testImagePropertyCommandGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/logo.jpg';
        $output = $this->runCommand('property', $path);
        $this->assertContains('Red:', $output);
        $this->assertContains('Green:', $output);
        $this->assertContains('Blue:', $output);
    }

    # tests for Vision 1.1 features
    public function testCropHintsCommand()
    {
        $path = __DIR__ . '/data/wakeupcat.jpg';
        $output = $this->runCommand('crop-hints', $path);
        $this->assertContains('Crop hints:', $output);
        $this->assertContains('(0,0)', $output);
        $this->assertContains('(599,0)', $output);
        $this->assertContains('(599,475)', $output);
        $this->assertContains('(0,475)', $output);
    }

    public function testCropHintsCommandGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/wakeupcat.jpg';
        $output = $this->runCommand('crop-hints', $path);
        $this->assertContains('Crop hints:', $output);
        $this->assertContains('(0,0)', $output);
        $this->assertContains('(599,0)', $output);
        $this->assertContains('(599,475)', $output);
        $this->assertContains('(0,475)', $output);
    }

    public function testDocumentTextCommand()
    {
        $path = __DIR__ . '/data/text.jpg';
        $output = $this->runCommand('document-text', $path);
        $this->assertContains('the PS4 will automatically restart', $output);
        $this->assertContains('37 %', $output);
        $this->assertContains('Block content:', $output);
        $this->assertContains('Bounds:', $output);
    }

    public function testDocumentTextCommandGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/text.jpg';
        $output = $this->runCommand('document-text', $path);
        $this->assertContains('the PS4 will automatically restart', $output);
        $this->assertContains('37 %', $output);
        $this->assertContains('Block content:', $output);
        $this->assertContains('Bounds:', $output);
    }

    public function testPdfGcs()
    {
        $this->requireCloudStorage();

        $source = 'gs://' . $this->bucketName . '/HodgeConj.pdf';
        $destination = 'gs://' . $this->bucketName . '/OCR_PDF_TEST_OUTPUT/';
        $output = $this->runCommand('pdf', $source, $destination);
        $this->assertContains('Output files:', $output);
    }

    public function testDetectWebNoGeoCommand()
    {
        $path = __DIR__ . '/data/geotagged.jpg';
        $output = $this->runCommand('web', $path);
        $this->assertContains('web entities found', $output);
        $this->assertNotRegExp('/^0 web entities found:/', $output);
    }

    public function testDetectWebNoGeoCommandGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/geotagged.jpg';
        $output = $this->runCommand('web', $path);
        $this->assertContains('web entities found', $output);
        $this->assertNotRegExp('/^0 web entities found:/', $output);
    }

    public function testDetectWebGeoCommand()
    {
        $path = __DIR__ . '/data/geotagged.jpg';
        $output = $this->runCommand('web-geo', $path);
        $this->assertContains('web entities found', $output);
        $this->assertNotRegExp('/^0 web entities found:/', $output);
    }

    public function testDetectWebGeoCommandGcs()
    {
        $this->requireCloudStorage();

        $path = 'gs://' . $this->bucketName . '/vision/geotagged.jpg';
        $output = $this->runCommand('web-geo', $path);
        $this->assertContains('web entities found', $output);
        $this->assertNotRegExp('/^0 web entities found:/', $output);
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

    private function requireCloudStorage()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GOOGLE_STORAGE_BUCKET environment variable');
        }
    }
}
