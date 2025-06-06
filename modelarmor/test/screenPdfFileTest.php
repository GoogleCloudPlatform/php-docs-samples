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

class screenPdfFileTest extends BaseSanitizeTestCase
{
    protected static function getTemplatePrefix(): string
    {
        return 'php-screen-pdf-';
    }

    public function testSanitizeUserPromptWithRaiTemplate()
    {
        $pdfFilePath = __DIR__ . '/test_sample.pdf';

        $output = $this->runSnippetfile('screen_pdf_file', [
            self::getProjectId(),
            self::$locationId,
            self::$testRaiTemplateId,
            $pdfFilePath
            ]);
        $expectedResult = '"filterMatchState":"NO_MATCH_FOUND"';
        $this->assertStringContainsString('Result for Screen PDF File:', $output);
        $this->assertStringContainsString($expectedResult, $output);
    }
}
