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

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnitRetry\RetryTrait;

/**
 * Unit Tests for vision commands.
 *
 * @retryAttempts 2
 */
class visionTest extends TestCase
{
    use TestTrait;
    use RetryTrait;

    public function testLabelCommand()
    {
        $path = __DIR__ . '/data/cat.jpg';
        $output = $this->runFunctionSnippet('detect_label', ['path' => $path]);
        $this->assertStringContainsString('cat', $output);
    }

    public function testLabelCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/cat.jpg';
        $output = $this->runFunctionSnippet('detect_label_gcs', ['path' => $path]);
        $this->assertStringContainsString('cat', $output);
    }

    public function testTextCommand()
    {
        $path = __DIR__ . '/data/sabertooth.gif';
        $output = $this->runFunctionSnippet('detect_text', ['path' => $path]);
        $this->assertStringContainsString('extinct', $output);
    }

    public function testTextCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/sabertooth.gif';
        $output = $this->runFunctionSnippet('detect_text_gcs', ['path' => $path]);
        $this->assertStringContainsString('extinct', $output);
    }

    public function testTextCommandWithImageLackingText()
    {
        $path = __DIR__ . '/data/cat.jpg';
        $output = $this->runFunctionSnippet('detect_text', ['path' => $path]);
        $this->assertStringContainsString('0 texts found', $output);
    }

    public function testTextCommandWithImageLackingTextGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/cat.jpg';
        $output = $this->runFunctionSnippet('detect_text_gcs', ['path' => $path]);
        $this->assertStringContainsString('0 texts found', $output);
    }

    public function testFaceCommand()
    {
        $path = __DIR__ . '/data/face.png';
        $output = $this->runFunctionSnippet('detect_face', ['path' => $path]);
        $this->assertStringContainsString('Anger: ', $output);
        $this->assertStringContainsString('Joy: ', $output);
        $this->assertStringContainsString('Surprise: ', $output);
    }

    public function testFaceCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/face.png';
        $output = $this->runFunctionSnippet('detect_face_gcs', ['path' => $path]);
        $this->assertStringContainsString('Anger: ', $output);
        $this->assertStringContainsString('Joy: ', $output);
        $this->assertStringContainsString('Surprise: ', $output);
    }

    public function testFaceCommandWithImageLackingFaces()
    {
        $path = __DIR__ . '/data/tower.jpg';
        $output = $this->runFunctionSnippet('detect_face', ['path' => $path]);
        $this->assertStringContainsString('0 faces found', $output);
    }

    public function testFaceCommandWithImageLackingFacesGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/tower.jpg';
        $output = $this->runFunctionSnippet('detect_face_gcs', ['path' => $path]);
        $this->assertStringContainsString('0 faces found', $output);
    }

    public function testLandmarkCommand()
    {
        $path = __DIR__ . '/data/tower.jpg';
        $output = $this->runFunctionSnippet('detect_landmark', ['path' => $path]);
        $this->assertRegexp(
            '/Eiffel Tower|Champ de Mars|Trocadéro Gardens/',
            $output
        );
    }

    public function testLandmarkCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/tower.jpg';
        $output = $this->runFunctionSnippet('detect_landmark_gcs', ['path' => $path]);
        $this->assertRegexp(
            '/Eiffel Tower|Champ de Mars|Trocadéro Gardens/',
            $output
        );
    }

    public function testLandmarkCommandWithImageLackingLandmarks()
    {
        $path = __DIR__ . '/data/faulkner.jpg';
        $output = $this->runFunctionSnippet('detect_landmark', ['path' => $path]);
        $this->assertStringContainsString('0 landmark found', $output);
    }

    public function testLandmarkCommandWithImageLackingLandmarksGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/faulkner.jpg';
        $output = $this->runFunctionSnippet('detect_landmark_gcs', ['path' => $path]);
        $this->assertStringContainsString('0 landmark found', $output);
    }

    public function testLogoCommand()
    {
        $path = __DIR__ . '/data/logo.jpg';
        $output = $this->runFunctionSnippet('detect_logo', ['path' => $path]);
        $this->assertStringContainsString('Google', $output);
    }

    public function testLogoCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/logo.jpg';
        $output = $this->runFunctionSnippet('detect_logo_gcs', ['path' => $path]);
        $this->assertStringContainsString('Google', $output);
    }

    public function testDetectObjectCommand()
    {
        $path = __DIR__ . '/data/puppies.jpg';
        $output = $this->runFunctionSnippet('detect_object', ['path' => $path]);
        $this->assertStringContainsString('Dog', $output);
    }

    public function testLocalizeObjectCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/puppies.jpg';
        $output = $this->runFunctionSnippet('detect_object_gcs', ['path' => $path]);
        $this->assertStringContainsString('Dog', $output);
    }

    public function testLogoCommandWithImageLackingLogo()
    {
        $path = __DIR__ . '/data/tower.jpg';
        $output = $this->runFunctionSnippet('detect_logo', ['path' => $path]);
        $this->assertStringContainsString('0 logos found', $output);
    }

    public function testLogoCommandWithImageLackingLogoGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/tower.jpg';
        $output = $this->runFunctionSnippet('detect_logo_gcs', ['path' => $path]);
        $this->assertStringContainsString('0 logos found', $output);
    }

    public function testSafeSearchCommand()
    {
        $path = __DIR__ . '/data/logo.jpg';
        $output = $this->runFunctionSnippet('detect_safe_search', ['path' => $path]);
        $this->assertStringContainsString('Adult:', $output);
        $this->assertStringContainsString('Racy:', $output);
    }

    public function testSafeSearchCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/logo.jpg';
        $output = $this->runFunctionSnippet('detect_safe_search_gcs', ['path' => $path]);
        $this->assertStringContainsString('Adult:', $output);
        $this->assertStringContainsString('Racy:', $output);
    }

    public function testImagePropertyCommand()
    {
        $path = __DIR__ . '/data/logo.jpg';
        $output = $this->runFunctionSnippet('detect_image_property', ['path' => $path]);
        $this->assertStringContainsString('Red:', $output);
        $this->assertStringContainsString('Green:', $output);
        $this->assertStringContainsString('Blue:', $output);
    }

    public function testImagePropertyCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/logo.jpg';
        $output = $this->runFunctionSnippet('detect_image_property_gcs', ['path' => $path]);
        $this->assertStringContainsString('Red:', $output);
        $this->assertStringContainsString('Green:', $output);
        $this->assertStringContainsString('Blue:', $output);
    }

    # tests for Vision 1.1 features

    public function testDocumentTextCommand()
    {
        $path = __DIR__ . '/data/text.jpg';
        $output = $this->runFunctionSnippet('detect_document_text', ['path' => $path]);
        $this->assertStringContainsString('the PS4 will automatically restart', $output);
        $this->assertStringContainsString('37 %', $output);
        $this->assertStringContainsString('Block content:', $output);
        $this->assertStringContainsString('Bounds:', $output);
    }

    public function testDocumentTextCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/text.jpg';
        $output = $this->runFunctionSnippet('detect_document_text_gcs', ['path' => $path]);
        $this->assertStringContainsString('the PS4 will automatically restart', $output);
        $this->assertStringContainsString('37 %', $output);
        $this->assertStringContainsString('Block content:', $output);
        $this->assertStringContainsString('Bounds:', $output);
    }

    public function testPdfGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $source = 'gs://' . $bucketName . '/vision/HodgeConj.pdf';
        $destination = 'gs://' . $bucketName . '/OCR_PDF_TEST_OUTPUT/';
        $output = $this->runFunctionSnippet('detect_pdf_gcs', [
            'path' => $source,
            'output' => $destination,
        ]);
        $this->assertStringContainsString('Output files:', $output);
    }

    public function testDetectWebNoGeoCommand()
    {
        $path = __DIR__ . '/data/geotagged.jpg';
        $output = $this->runFunctionSnippet('detect_web', ['path' => $path]);
        $this->assertStringContainsString('web entities found', $output);
        $this->assertNotRegExp('/^0 web entities found:/', $output);
    }

    public function testDetectWebNoGeoCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/geotagged.jpg';
        $output = $this->runFunctionSnippet('detect_web_gcs', ['path' => $path]);
        $this->assertStringContainsString('web entities found', $output);
        $this->assertNotRegExp('/^0 web entities found:/', $output);
    }

    public function testDetectWebGeoCommand()
    {
        $path = __DIR__ . '/data/geotagged.jpg';
        $output = $this->runFunctionSnippet('detect_web_with_geo_metadata', ['path' => $path]);
        $this->assertStringContainsString('web entities found', $output);
        $this->assertNotRegExp('/^0 web entities found:/', $output);
    }

    public function testDetectWebGeoCommandGcs()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $path = 'gs://' . $bucketName . '/vision/geotagged.jpg';
        $output = $this->runFunctionSnippet('detect_web_with_geo_metadata_gcs', ['path' => $path]);
        $this->assertStringContainsString('web entities found', $output);
        $this->assertNotRegExp('/^0 web entities found:/', $output);
    }
}
