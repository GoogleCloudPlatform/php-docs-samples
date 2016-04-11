<?php
/**
 * Copyright 2015 Google Inc.
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

# [START get_current_user]
use google\appengine\api\users\User;
use google\appengine\api\users\UserService;
use Silex\Application;

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    $user = UserService::getCurrentUser();

    if (isset($user)) {
        return sprintf('Welcome, %s! (<a href="%s">sign out</a>)',
            $user->getNickname(),
            UserService::createLogoutUrl('/'));
    } else {
        return sprintf('<a href="%s">Sign in or register</a>',
            UserService::createLoginUrl('/'));
    }
});
# [END get_current_user]

return $app;
