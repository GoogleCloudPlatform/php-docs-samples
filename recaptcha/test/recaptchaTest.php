<?php
/*
 * Copyright 2020 Google LLC.
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

namespace Google\Cloud\Samples\Recaptcha;

use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings\IntegrationType;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class recaptchaTest extends TestCase
{
    use TestTrait;

    private static $keyName;
    private static $keyId;

    public static function setUpBeforeClass(): void
    {
        self::$keyName = uniqid('php-snippets-key-');
    }

    public function testCreateKey()
    {
        $output = $this->runFunctionSnippet('create_key', [
            self::$projectId,
            self::$keyName
        ]);

        // since we need the value from the output string we don't use assertRegExp
        preg_match('/The key: projects\/.+\/keys\/(.+) is created\./', trim($output), $matches);
        if (count($matches)<2) {
            $this->fail();
        }

        // extract keyId from the output
        self::$keyId = $matches[1];
        $this->assertTrue(true);
    }

    /**
     * @depends testCreateKey
     */
    public function testListKeys()
    {
        $output = $this->runFunctionSnippet('list_keys', [
            self::$projectId
        ]);

        $array = explode(PHP_EOL, $output);

        $this->assertContains('Keys fetched', $array);
        $this->assertContains(self::$keyName, $array);
    }

    /**
     * @depends testCreateKey
     */
    public function testGetKey()
    {
        $output = $this->runFunctionSnippet('get_key', [
            self::$projectId,
            self::$keyId
        ]);

        $array = explode(PHP_EOL, $output);

        $this->assertContains('Key fetched', $array);
        $this->assertContains('Display name: ' . self::$keyName, $array);
        $this->assertContains('Web platform settings: Yes', $array);
        $this->assertContains('Allowed all domains: Yes', $array);
        $this->assertContains('Integration Type: ' . IntegrationType::name(IntegrationType::CHECKBOX), $array);
    }

    /**
     * @depends testCreateKey
     */
    public function testUpdateKey()
    {
        $updatedName = self::$keyName . '-updated';
        $output = $this->runFunctionSnippet('update_key', [
            self::$projectId,
            self::$keyId,
            $updatedName
        ]);

        $this->assertSame('The key: ' . $updatedName . ' is updated.', trim($output));
    }

    /**
     * @depends testCreateKey
     */
    public function testDeleteKey()
    {
        $output = $this->runFunctionSnippet('delete_key', [
            self::$projectId,
            self::$keyId
        ]);

        $this->assertSame('The key: ' . self::$keyId . ' is deleted.', trim($output));
    }
}
