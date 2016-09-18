<?php

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
