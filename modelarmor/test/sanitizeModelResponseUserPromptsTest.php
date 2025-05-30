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

class sanitizeModelResponseUserPromptsTest extends BaseSanitizeTestCase
{
    protected static function getTemplatePrefix(): string
    {
        return 'php-model-response-user-prompt-';
    }

    public function testSanitizeModelResponseUserPromptWithRaiTemplate()
    {
        $userPrompt = 'How can I make my email address test@dot.com make available to public for feedback';
        $modelResponse = 'You can make support email such as contact@email.com for getting feedback from your customer';
        $output = $this->runSnippetfile('sanitize_model_response_with_user_prompt', [
            self::getProjectId(),
            self::$locationId,
            self::$testRaiTemplateId,
            $modelResponse,
            $userPrompt
            ]);
        $expectedResult = '"rai":{"raiFilterResult":{"executionState":"EXECUTION_SUCCESS","matchState":"NO_MATCH_FOUND","raiFilterTypeResults":{"sexually_explicit":{"matchState":"NO_MATCH_FOUND"},"hate_speech":{"matchState":"NO_MATCH_FOUND"},"harassment":{"matchState":"NO_MATCH_FOUND"},"dangerous":{"matchState":"NO_MATCH_FOUND"}}}}';
        $this->assertStringContainsString('Result for Model Response Sanitization with User Prompt:', $output);
        $this->assertStringContainsString($expectedResult, $output);
    }

    public function testSanitizeModelResponseUserPromptWithBasicSdpTemplate()
    {
        $userPrompt = 'How can I make my email address test@dot.com make available to public for feedback';
        $modelResponse = 'You can make support email such as contact@email.com for getting feedback from your customer';
        $output = $this->runSnippetfile('sanitize_model_response_with_user_prompt', [
            self::getProjectId(),
            self::$locationId,
            self::$testbasicSdpTemplateId,
            $modelResponse,
            $userPrompt
        ]);
        $expectedResult = '"sdp":{"sdpFilterResult":{"inspectResult":{"executionState":"EXECUTION_SUCCESS","matchState":"NO_MATCH_FOUND"}}}';
        $this->assertStringContainsString('Result for Model Response Sanitization with User Prompt:', $output);
        $this->assertStringContainsString($expectedResult, $output);
    }

    public function testSanitizeModelResponseUserPromptWithAdvancedSdpTemplate()
    {
        $userPrompt = 'How can I make my email address test@dot.com make available to public for feedback';
        $modelResponse = 'You can make support email such as contact@email.com for getting feedback from your customer';
        $output = $this->runSnippetfile('sanitize_model_response_with_user_prompt', [
            self::getProjectId(),
            self::$locationId,
            self::$testAdvanceSdpTemplateId,
            $modelResponse,
            $userPrompt
            ]);
        $expectedResult = '"sdp":{"sdpFilterResult":{"deidentifyResult":{"executionState":"EXECUTION_SUCCESS","matchState":"MATCH_FOUND","data":{"text":"You can make support email such as [REDACTED] for getting feedback from your customer"},"transformedBytes":"17","infoTypes":["EMAIL_ADDRESS"]}}}';
        $this->assertStringContainsString('Result for Model Response Sanitization with User Prompt:', $output);
        $this->assertStringContainsString($expectedResult, $output);
    }
}
