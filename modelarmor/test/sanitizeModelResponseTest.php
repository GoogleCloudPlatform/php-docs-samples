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

class sanitizeModelResponseTest extends BaseSanitizeTestCase
{
    protected static function getTemplatePrefix(): string
    {
        return 'php-sanitize-model-response-';
    }

    public function testSanitizeModelResponseWithRaiTemplate()
    {
        $modelResponse = "To make cheesecake without oven, you'll need to follow these steps...";
        $output = $this->runSnippetfile('sanitize_model_response', [
            self::getProjectId(),
            self::$locationId,
            self::$testRaiTemplateId,
            $modelResponse
        ]);
        $expectedResult = '"rai":{"raiFilterResult":{"executionState":"EXECUTION_SUCCESS","matchState":"NO_MATCH_FOUND","raiFilterTypeResults":{"sexually_explicit":{"matchState":"NO_MATCH_FOUND"},"hate_speech":{"matchState":"NO_MATCH_FOUND"},"harassment":{"matchState":"NO_MATCH_FOUND"},"dangerous":{"matchState":"NO_MATCH_FOUND"}}}}';
        $this->assertStringContainsString('Result for Model Response Sanitization:', $output);
        $this->assertStringContainsString($expectedResult, $output);
    }

    public function testSanitizeModelResponseWithMaliciousUrlTemplate()
    {
        $modelResponse = 'You can use this to make a cake: https://testsafebrowsing.appspot.com/s/malware.html';
        $output = $this->runSnippetfile('sanitize_model_response', [
            self::getProjectId(),
            self::$locationId,
            self::$testMaliciousTemplateId,
            $modelResponse
        ]);
        $expectedResult = '"malicious_uris":{"maliciousUriFilterResult":{"executionState":"EXECUTION_SUCCESS","matchState":"MATCH_FOUND","maliciousUriMatchedItems":[{"uri":"https:\/\/testsafebrowsing.appspot.com\/s\/malware.html","locations":[{"start":"33","end":"84"}]}]}}';
        $this->assertStringContainsString('Result for Model Response Sanitization:', $output);
        $this->assertStringContainsString($expectedResult, $output);
    }

    public function testSanitizeModelResponseWithCsamTemplate()
    {
        $userPrompt = 'Here is how to teach long division to a child';
        $output = $this->runSnippetfile('sanitize_model_response', [
            self::getProjectId(),
            self::$locationId,
            self::$testRaiTemplateId,
            $userPrompt
        ]);
        $expectedResult = '"csam":{"csamFilterFilterResult":{"executionState":"EXECUTION_SUCCESS","matchState":"NO_MATCH_FOUND"}}';
        $this->assertStringContainsString('Result for Model Response Sanitization:', $output);
        $this->assertStringContainsString($expectedResult, $output);
    }

    public function testSanitizeModelResponseWithBasicSdpTemplate()
    {
        $modelResponse = 'For following email 1l6Y2@example.com found following associated phone number: 954-321-7890 and this ITIN: 988-86-1234';
        $output = $this->runSnippetfile('sanitize_model_response', [
            self::getProjectId(),
            self::$locationId,
            self::$testbasicSdpTemplateId,
            $modelResponse
        ]);
        $expectedResult = '"sdp":{"sdpFilterResult":{"inspectResult":{"executionState":"EXECUTION_SUCCESS","matchState":"MATCH_FOUND","findings":[{"infoType":"US_INDIVIDUAL_TAXPAYER_IDENTIFICATION_NUMBER","likelihood":"LIKELY","location":{"byteRange":{"start":"107","end":"118"},"codepointRange":{"start":"107","end":"118"}}}]}}}';
        $this->assertStringContainsString('Result for Model Response Sanitization:', $output);
        $this->assertStringContainsString($expectedResult, $output);
    }

    public function testSanitizeModelResponseWithAdvancedSdpTemplate()
    {
        $modelResponse = 'For following email 1l6Y2@example.com found following associated phone number: 954-321-7890 and this ITIN: 988-86-1234';
        $output = $this->runSnippetfile('sanitize_model_response', [
            self::getProjectId(),
            self::$locationId,
            self::$testAdvanceSdpTemplateId,
            $modelResponse
        ]);
        $expectedResult = '"sdp":{"sdpFilterResult":{"deidentifyResult":{"executionState":"EXECUTION_SUCCESS","matchState":"MATCH_FOUND","data":{"text":"For following email [REDACTED] found following associated phone number: [REDACTED] and this ITIN: [REDACTED]"},"transformedBytes":"40","infoTypes":["EMAIL_ADDRESS","PHONE_NUMBER","US_INDIVIDUAL_TAXPAYER_IDENTIFICATION_NUMBER"]}}}';
        $this->assertStringContainsString('Result for Model Response Sanitization:', $output);
        $this->assertStringContainsString($expectedResult, $output);
    }
}
