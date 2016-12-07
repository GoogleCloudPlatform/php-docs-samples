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
namespace Google\Cloud\Samples\Speech;

/**
 * This file is to be used as an example only!
 *
 * Usage:
 * ```
 * $audioFile = '/path/to/YourAudio.raw';
 * $base64Audio = require '/path/to/base64_encode_audio.php';
 * ```
 */
# [START base64_audio]
$audioFileResource = fopen($audioFile, 'r');
$base64Audio = base64_encode(stream_get_contents($audioFileResource));
# [end base64_audio]
return $base64Audio;
