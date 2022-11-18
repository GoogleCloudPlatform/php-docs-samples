<?php

/**
 * Copyright 2022 Google LLC.
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

namespace Google\Cloud\Samples\Media\Stitcher;

use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\Video\Stitcher\V1\VideoStitcherServiceClient;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for Video Stitcher commands.
 */
class videoStitcherTest extends TestCase
{
    use TestTrait;
    use EventuallyConsistentTestTrait;

    private static $projectId;
    private static $location = 'us-central1';

    private static $bucket = 'cloud-samples-data/media/';
    private static $slateFileName = 'ForBiggerEscapes.mp4';
    private static $updatedSlateFileName = 'ForBiggerJoyrides.mp4';

    private static $slateUri;
    private static $updatedSlateUri;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');

        self::deleteOldSlates();

        self::$slateUri = sprintf('https://storage.googleapis.com/%s%s', self::$bucket, self::$slateFileName);
        self::$updatedSlateUri = sprintf('https://storage.googleapis.com/%s%s', self::$bucket, self::$updatedSlateFileName);
    }

    public function testSlates()
    {
        $slateId = sprintf('php-test-slate-%s', time());
        # API returns project number rather than project ID so
        # don't include that in $slateName since we don't have it
        $slateName = sprintf('/locations/%s/slates/%s', self::$location, $slateId);

        $output = $this->runFunctionSnippet('create_slate', [
            self::$projectId,
            self::$location,
            $slateId,
            self::$slateUri
        ]);
        $this->assertStringContainsString($slateName, $output);

        $output = $this->runFunctionSnippet('get_slate', [
            self::$projectId,
            self::$location,
            $slateId
        ]);
        $this->assertStringContainsString($slateName, $output);

        $output = $this->runFunctionSnippet('list_slates', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString($slateName, $output);

        $output = $this->runFunctionSnippet('update_slate', [
            self::$projectId,
            self::$location,
            $slateId,
            self::$updatedSlateUri
        ]);
        $this->assertStringContainsString($slateName, $output);

        $output = $this->runFunctionSnippet('delete_slate', [
            self::$projectId,
            self::$location,
            $slateId
        ]);
        $this->assertStringContainsString('Deleted slate', $output);
    }

    public static function deleteOldSlates(): void
    {
        $stitcherClient = new VideoStitcherServiceClient();
        $parent = $stitcherClient->locationName(self::$projectId, self::$location);
        $response = $stitcherClient->listSlates($parent);
        $slates = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($slates as $slate) {
            $tmp = explode('/', $slate->getName());
            $id = end($tmp);
            $tmp = explode('-', $id);
            $timestamp = intval(end($tmp));

            if ($currentTime - $timestamp >= $oneHourInSecs) {
                $stitcherClient->deleteSlate($slate->getName());
            }
        }
    }
}
