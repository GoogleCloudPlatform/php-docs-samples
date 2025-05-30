<?php
/*
 * Copyright 2025 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
*/

declare(strict_types=1);

namespace Google\Cloud\Samples\ModelArmor;

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class updateFolderFloorSettingsTest extends TestCase
{
    use TestTrait;

    private static $folderId;

    public static function setUpBeforeClass(): void
    {
        self::$folderId = getenv("MA_FOLDER_ID");
    }
    public function testUpdateFolderFloorSettings()
    {
        $output = $this->runSnippet('update_folder_floor_settings', [
            self::$folderId,
        ]);

        $expectedResponseString = 'Floor settings retrieved successfully:';
        $this->assertStringContainsString($expectedResponseString, $output);
    }
}
