<?php
/**
 * Copyright 2022 Google LLC
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

namespace Google\Cloud\Samples\Compute;

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class firewallTest extends TestCase
{
    use TestTrait;

    private static $firewallRuleName;
    private static $priority;

    private const DEFAULT_ZONE = 'us-central1-a';

    public static function setUpBeforeClass(): void
    {
        self::$firewallRuleName = sprintf('test-firewall-rule-%s', rand());
        self::$priority = 20;
    }

    public function testCreateFirewallRule()
    {
        $output = $this->runFunctionSnippet('create_firewall_rule', [
            'projectId' => self::$projectId,
            'firewallRuleName' => self::$firewallRuleName
        ]);
        $this->assertStringContainsString('Created rule ' . self::$firewallRuleName, $output);
    }

    /**
     * @depends testCreateFirewallRule
     */
    public function testPrintFirewallRule()
    {
        $output = $this->runFunctionSnippet('print_firewall_rule', [
            'projectId' => self::$projectId,
            'firewallRuleName' => self::$firewallRuleName
        ]);
        $this->assertStringContainsString(self::$firewallRuleName, $output);
        $this->assertStringContainsString('0.0.0.0/0', $output);
    }

    /**
     * @depends testCreateFirewallRule
     */
    public function testListFirewallRules()
    {
        $output = $this->runFunctionSnippet('list_firewall_rules', [
            'projectId' => self::$projectId
        ]);
        $this->assertStringContainsString(self::$firewallRuleName, $output);
        $this->assertStringContainsString('Allowing TCP traffic on ports 80 and 443 from Internet.', $output);
    }

    /**
     * @depends testCreateFirewallRule
     */
    public function testPatchFirewallPriority()
    {
        $output = $this->runFunctionSnippet('patch_firewall_priority', [
            'projectId' => self::$projectId,
            'firewallRuleName' => self::$firewallRuleName,
            'priority' => self::$priority
        ]);
        $this->assertStringContainsString('Patched ' . self::$firewallRuleName . ' priority', $output);
    }
    /**
     * @depends testPrintFirewallRule
     * @depends testListFirewallRules
     * @depends testPatchFirewallPriority
     */
    public function testDeleteFirewallRule()
    {
        $output = $this->runFunctionSnippet('delete_firewall_rule', [
            'projectId' => self::$projectId,
            'firewallRuleName' => self::$firewallRuleName
        ]);
        $this->assertStringContainsString('Rule ' . self::$firewallRuleName . ' deleted',  $output);
    }
}
