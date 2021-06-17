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
declare(strict_types=1);

namespace Google\Cloud\Samples\Functions\ImageMagick\Test;

use Google\CloudFunctions\CloudEvent;
use Google\Cloud\TestUtils\TestTrait;

trait TestCasesTrait
{
    use TestTrait;

    /** @var string */
    private static $entryPoint = 'blurOffensiveImages';

    /** @var string */
    private static $functionSignatureType = 'cloudevent';

    public static function getDataForFile($fileName): array
    {
        return [
            'bucket' => self::requireEnv('GOOGLE_STORAGE_BUCKET'),
            'metageneration' => '1',
            'name' => $fileName,
            'timeCreated' => '2020-04-23T07:38:57.230Z',
            'updated' => '2020-04-23T07:38:57.230Z',
            'statusCode' => '200'
        ];
    }

    public static function cases(): array
    {
        $bucketName = self::requireEnv('BLURRED_BUCKET_NAME');

        return [
            [
                'cloudevent' => CloudEvent::fromArray([
                    'id' => uniqid(),
                    'source' => 'storage.googleapis.com',
                    'specversion' => '1.0',
                    'type' => 'google.cloud.storage.object.v1.finalized',
                    'data' => TestCasesTrait::getDataForFile('functions/puppies.jpg'),
                ]),
                'label' => 'Ignores safe images',
                'fileName' => 'functions/puppies.jpg',
                'expected' => 'Detected functions/puppies.jpg as OK',
                'statusCode' => '200'
            ],
            [
                'cloudevent' => CloudEvent::fromArray([
                    'id' => uniqid(),
                    'source' => 'storage.googleapis.com',
                    'specversion' => '1.0',
                    'type' => 'google.cloud.storage.object.v1.finalized',
                    'data' => TestCasesTrait::getDataForFile('functions/zombie.jpg'),
                ]),
                'label' => 'Blurs offensive images',
                'fileName' => 'functions/zombie.jpg',
                'expected' => sprintf(
                    'Streamed blurred image to: gs://%s/functions/zombie.jpg',
                    $bucketName
                ),
                'statusCode' => '200'
            ],
        ];
    }

    public static function integrationCases(): array
    {
        $bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');

        return [
            [
                'cloudevent' => CloudEvent::fromArray([
                    'id' => uniqid(),
                    'source' => 'storage.googleapis.com',
                    'specversion' => '1.0',
                    'type' => 'google.cloud.storage.object.v1.finalized',
                    'data' => TestCasesTrait::getDataForFile('does-not-exist.jpg')
                ]),
                'label' => 'Labels missing images as safe',
                'filename' => 'does-not-exist.jpg',
                'expected' => sprintf(
                    'NOT_FOUND: Error opening file: gs://%s/does-not-exist.jpg',
                    $bucketName
                ),
                'statusCode' => '200'
            ],
        ];
    }
}
