<?php
/**
 * Copyright 2016 Google Inc.
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
use google\appengine\api\users\User;
use google\appengine\api\users\UserService;
use Silex\WebTestCase;

require_once __DIR__ . '/mocks/User.php';
require_once __DIR__ . '/mocks/UserService.php';

class UsersApiTest extends WebTestCase
{
    private $user;

    public function createApplication()
    {
        $app = require __DIR__ . '/../../app.php';

        // Create mock user
        $this->user = $this->getMockBuilder('google\appengine\api\users\User')
            ->disableOriginalConstructor()
            ->setMethods(array('getNickname'))
            ->getMock();

        // prevent HTML error exceptions
        unset($app['exception_handler']);

        return $app;
    }

    public function testLoginUrl()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains(
            UserService::$loginUrl,
            $client->getResponse()->getContent());
    }

    public function testAdmin()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/admin');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains(
            'You are not an administrator.',
            $client->getResponse()->getContent());
    }

    public function testAdminWithAdminUser()
    {
        $client = $this->createClient();
        $nickname = 'tmatsuo';
        $this->user->method('getNickname')->willReturn($nickname);
        UserService::$user = $this->user;
        $ret = putenv('USER_IS_ADMIN=1');
        $client->request('GET', '/admin');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains(
            'Welcome administrator.',
            $client->getResponse()->getContent());
    }

    public function testLogoutUrl()
    {
        $nickname = 'tmatsuo';
        $this->user->method('getNickname')->willReturn($nickname);
        $this->user->expects($this->once())->method('getNickname');
        UserService::$user = $this->user;
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
        $body = $client->getResponse()->getContent();
        $this->assertContains(UserService::$logoutUrl, $body);
        $this->assertContains($nickname, $body);
    }

    public function testUser()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/user');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains(
            'Nickname is Albert.Johnson',
            $client->getResponse()->getContent());
    }

    public function testFederatedUser()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/federatedUser');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains(
            'Nickname is http://example.com/id/ajohnson',
            $client->getResponse()->getContent());
    }
}
