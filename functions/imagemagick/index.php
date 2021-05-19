<?php
/**
 * Copyright 2020 Google LLC.
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

// [START functions_imagemagick_setup]
use Google\CloudFunctions\CloudEvent;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Likelihood;
use Google\Rpc\Code;

// [END functions_imagemagick_setup]

// [START functions_imagemagick_analyze]
function blurOffensiveImages(CloudEvent $cloudevent): void
{
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');

    $storage = new StorageClient();
    $data = $cloudevent->getData();

    $file = $storage->bucket($data['bucket'])->object($data['name']);
    $filePath = 'gs://' . $data['bucket'] . '/' . $data['name'];
    fwrite($log, 'Analyzing ' . $filePath . PHP_EOL);

    $annotator = new ImageAnnotatorClient();
    $storage = new StorageClient();

    try {
        $response = $annotator->safeSearchDetection($filePath);

        // Handle error
        if ($response->hasError()) {
            $code = Code::name($response->getError()->getCode());
            $message = $response->getError()->getMessage();
            fwrite($log, sprintf('%s: %s' . PHP_EOL, $code, $message));
            return;
        }

        $annotation = $response->getSafeSearchAnnotation();

        $isInappropriate =
            $annotation->getAdult() === Likelihood::VERY_LIKELY ||
            $annotation->getViolence() === Likelihood::VERY_LIKELY;

        if ($isInappropriate) {
            fwrite($log, 'Detected ' . $data['name'] . ' as inappropriate.' . PHP_EOL);
            $blurredBucketName = getenv('BLURRED_BUCKET_NAME');

            blurImage($log, $file, $blurredBucketName);
        } else {
            fwrite($log, 'Detected ' . $data['name'] . ' as OK.' . PHP_EOL);
        }
    } catch (Exception $e) {
        fwrite($log, 'Failed to analyze ' . $data['name'] . PHP_EOL);
        fwrite($log, $e->getMessage() . PHP_EOL);
    }
}
// [END functions_imagemagick_analyze]

// [START functions_imagemagick_blur]
// Blurs the given file using ImageMagick, and uploads it to another bucket.
function blurImage(
    $log,
    Object $file,
    string $blurredBucketName
): void {
    $tempLocalPath = sys_get_temp_dir() . '/' . $file->name();

    // Download file from bucket.
    $image = new Imagick();
    try {
        $image->readImageBlob($file->downloadAsStream());
    } catch (Exception $e) {
        throw new Exception('Streaming download failed: ' . $e);
    }

    // Blur file using ImageMagick
    // (The Imagick class is from the PECL 'imagick' package)
    $image->blurImage(0, 16);

    // Stream blurred image result to a different bucket. // (This avoids re-triggering this function.)
    $storage = new StorageClient();
    $blurredBucket = $storage->bucket($blurredBucketName);

    // Upload the Blurred image back into the bucket.
    $gcsPath = 'gs://' . $blurredBucketName . '/' . $file->name();
    try {
        $blurredBucket->upload($image->getImageBlob(), [
            'name' => $file->name()
        ]);
        fwrite($log, 'Streamed blurred image to: ' . $gcsPath . PHP_EOL);
    } catch (Exception $e) {
        throw new Exception(
            sprintf(
                'Unable to stream blurred image to %s: %s',
                $gcsPath,
                $e->getMessage()
            )
        );
    }
}
// [END functions_imagemagick_blur]
