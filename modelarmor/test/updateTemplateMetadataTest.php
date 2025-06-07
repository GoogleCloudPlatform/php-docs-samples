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

class updateTemplateMetadataTest extends BaseTestCase
{
    protected static function getTemplatePrefix(): string
    {
        return 'php-update-template-metadata-';
    }

    public function testUpdateTemplateMetadata()
    {
        $projectId = self::getProjectId();

        // Create template with metadata before updating it.
        $this->runSnippetfile('create_template_with_metadata', [
            $projectId,
            self::$locationId,
            self::$templateId,
        ]);

        $output = $this->runSnippetfile('update_template_metadata', [
            $projectId,
            self::$locationId,
            self::$templateId,
        ]);

        $expectedTemplateString = 'Template updated: projects/' . $projectId . '/locations/' . self::$locationId . '/templates/' . self::$templateId;
        $this->assertStringContainsString($expectedTemplateString, $output);
    }
}
