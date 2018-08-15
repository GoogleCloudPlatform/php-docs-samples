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

namespace google\appengine\api\mail;

class Message
{
    private $attachments =[];
    private $sender = '';
    private $subject = '';
    private $textBody = '';
    private $to =[];

    public function addAttachment($name, $data, $id)
    {
        $obj = [
            'name' => $name,
            'data' => $data,
            'id' => $id,
        ];

        array_push($this->attachments, $obj);
    }

    public function addTo($email)
    {
        array_push($this->to, $email);
    }

    public function setSender($email)
    {
        $this->sender = $email;
    }

    public function setSubject($string)
    {
        $this->subject = $string;
    }

    public function setTextBody($string)
    {
        $this->textBody = $string;
    }

    public function send()
    {
    }
}
