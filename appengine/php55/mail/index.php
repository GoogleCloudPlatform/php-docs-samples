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

// [START gae_mail_api]
use google\appengine\api\mail\Message;

// Notice that $image_content_id is the optional Content-ID header value of the
// attachment. Must be enclosed by angle brackets (<>)
$image_content_id = '<image-content-id>';

// Pull in the raw file data of the image file to attach it to the message.
$image_data = file_get_contents('image.jpg');

try {
    $message = new Message();
    $message->setSender('from@example.com');
    $message->addTo('to@example.com');
    $message->setSubject('Example email');
    $message->setTextBody('Hello, world!');
    $message->addAttachment('image.jpg', $image_data, $image_content_id);
    $message->send();
    echo 'Mail Sent';
} catch (InvalidArgumentException $e) {
    echo 'There was an error';
}
// [END gae_mail_api]
