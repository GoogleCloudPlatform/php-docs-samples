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
$base64Audio = base64_encode(stream_get_contents($audioFile));
# [end base64_audio]
return $base64Audio;
