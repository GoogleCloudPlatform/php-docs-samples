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

namespace google\appengine\api\users;

/**
 * Simple mock class for App Engine User object
 */
class User
{
    private $email;
    private $url;

    /**
     * A simple constructor for the mock User object.
     */
    public function __construct(
            $email = null,
            $federated_identity = null,
            $federated_provider = null,
            $user_id = null)
    {
        if ($email === null and $federated_identity === null) {
            throw new \InvalidArgumentException(
                'One of $email or $federated_identity must be set.');
        }
        $this->email = $email;
        $this->federated_identity = $federated_identity;
        $this->federated_provider = $federated_provider;
        $this->user_id = $user_id;
    }

    /**
     * Returns the user's nickname.
     *
     * @return string
     */
    public function getNickname()
    {
        if ($this->email !== null) {
            return explode('@', $this->email)[0];
        }
        return $this->federated_identity;
    }
}
