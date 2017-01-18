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
        $application = new Application();
        $application->add(new DetectLabelCommand());
        $commandTester = new CommandTester($application->get('label'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/cat.jpg',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('cat', $display);
        $this->assertContains('mammal', $display);
    }

    public function testLabelCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $application = new Application();
        $application->add(new DetectLabelCommand());
        $commandTester = new CommandTester($application->get('label'));
        $commandTester->execute(
            [
                'path' => "gs://{$this->bucketName}/cat.jpg",
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('cat', $display);
        $this->assertContains('mammal', $display);
    }

    public function testTextCommand()
    {
        $application = new Application();
        $application->add(new DetectTextCommand());
        $commandTester = new CommandTester($application->get('text'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/sabertooth.gif',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('extinct', $display);
    }

    public function testTextCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $application = new Application();
        $application->add(new DetectTextCommand());
        $commandTester = new CommandTester($application->get('text'));
        $commandTester->execute(
            [
                'path' => "gs://{$this->bucketName}/sabertooth.gif",
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('extinct', $display);
    }

    public function testTextCommandWithImageLackingText()
    {
        $application = new Application();
        $application->add(new DetectTextCommand());
        $commandTester = new CommandTester($application->get('text'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/faulkner.jpg',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertEquals("Texts:\n", $display);
    }

    public function testFaceCommand()
    {
        $application = new Application();
        $application->add(new DetectFaceCommand());
        $commandTester = new CommandTester($application->get('face'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/face.png',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('Anger: ', $display);
        $this->assertContains('Joy: ', $display);
        $this->assertContains('Surprise: ', $display);
    }

    public function testFaceCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $application = new Application();
        $application->add(new DetectFaceCommand());
        $commandTester = new CommandTester($application->get('face'));
        $commandTester->execute(
            [
                'path' => "gs://{$this->bucketName}/face.png",
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('Anger: ', $display);
        $this->assertContains('Joy: ', $display);
        $this->assertContains('Surprise: ', $display);
    }

    public function testFaceCommandWithImageLackingFaces()
    {
        $application = new Application();
        $application->add(new DetectFaceCommand());
        $commandTester = new CommandTester($application->get('face'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/tower.jpg',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertEquals("Faces:\n", $display);
    }

    public function testFaceCommandWithOutput()
    {
        $application = new Application();
        $application->add(new DetectFaceCommand());
        $commandTester = new CommandTester($application->get('face'));
        $output = sys_get_temp_dir() . '/face.png';
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/face.png',
                'output' => $output,
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('Anger: ', $display);
        $this->assertContains('Joy: ', $display);
        $this->assertContains('Surprise: ', $display);
        $this->assertContains('Output image written to ' . $output, $display);
        $this->assertTrue(file_exists($output));
    }

    public function testFaceCommandWithImageLackingFacesAndOutput()
    {
        $application = new Application();
        $application->add(new DetectFaceCommand());
        $commandTester = new CommandTester($application->get('face'));
        $output = sys_get_temp_dir() . '/face.png';
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/tower.jpg',
                'output' => $output,
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertEquals("Faces:\n", $display);
    }

    public function testLandmarkCommand()
    {
        $application = new Application();
        $application->add(new DetectLandmarkCommand());
        $commandTester = new CommandTester($application->get('landmark'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/tower.jpg',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('Eiffel', $display);
    }

    public function testLandmarkCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $application = new Application();
        $application->add(new DetectLandmarkCommand());
        $commandTester = new CommandTester($application->get('landmark'));
        $commandTester->execute(
            [
                'path' => "gs://{$this->bucketName}/tower.jpg",
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('Eiffel', $display);
    }

    public function testLandmarkCommandWithImageLackingLandmarks()
    {
        $application = new Application();
        $application->add(new DetectLandmarkCommand());
        $commandTester = new CommandTester($application->get('landmark'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/faulkner.jpg',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertEquals("Landmarks:\n", $display);
    }

    public function testLogoCommand()
    {
        $application = new Application();
        $application->add(new DetectLogoCommand());
        $commandTester = new CommandTester($application->get('logo'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/logo.jpg',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('Google', $display);
    }

    public function testLogoCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $application = new Application();
        $application->add(new DetectLogoCommand());
        $commandTester = new CommandTester($application->get('logo'));
        $commandTester->execute(
            [
                'path' => "gs://{$this->bucketName}/logo.jpg",
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('Google', $display);
    }

    public function testLogoCommandWithImageLackingLogo()
    {
        $application = new Application();
        $application->add(new DetectLogoCommand());
        $commandTester = new CommandTester($application->get('logo'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/tower.jpg',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertEquals("Logos:\n", $display);
    }

    public function testSafeSearchCommand()
    {
        $application = new Application();
        $application->add(new DetectSafeSearchCommand());
        $commandTester = new CommandTester($application->get('safe-search'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/logo.jpg',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('Adult:', $display);
    }

    public function testSafeSearchCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $application = new Application();
        $application->add(new DetectSafeSearchCommand());
        $commandTester = new CommandTester($application->get('safe-search'));
        $commandTester->execute(
            [
                'path' => "gs://{$this->bucketName}/logo.jpg",
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('Adult:', $display);
    }

    public function testImagePropertyCommand()
    {
        $application = new Application();
        $application->add(new DetectImagePropertyCommand());
        $commandTester = new CommandTester($application->get('property'));
        $commandTester->execute(
            [
                'path' => __DIR__ . '/data/logo.jpg',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('red:', $display);
        $this->assertContains('green:', $display);
        $this->assertContains('blue:', $display);
    }

    public function testImagePropertyCommandGcs()
    {
        if (!$this->bucketName) {
            $this->markTestSkipped('Set the GCS_BUCKET_NAME environment variable');
        }
        $application = new Application();
        $application->add(new DetectImagePropertyCommand());
        $commandTester = new CommandTester($application->get('property'));
        $commandTester->execute(
            [
                'path' => "gs://{$this->bucketName}/logo.jpg",
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $this->getActualOutput();
        $this->assertContains('red:', $display);
        $this->assertContains('green:', $display);
        $this->assertContains('blue:', $display);
    }
}
