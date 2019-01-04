<?php
/*
 * Copyright 2018 Google Inc.
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

# [START signed_url]
/**
 * Creates signed URL for Google Cloud CDN
 * Details about order of operations: https://cloud.google.com/cdn/docs/using-signed-urls#creating_signed_urls
 *
 * @param string $url             URL of the endpoint served by Cloud CDN
 * @param string $keyName         Name of the signing key added to the Google Cloud Storage bucket or service
 * @param string $key             Signing key as base64 encoded string
 * @param int    $expiration_time Expiration time as a UNIX timestamp (GMT, e.g. time())
 */
function signUrl($url, $keyName, $key, $expiration_time)
{
    // Decode the key
    $decoded_key = base64_decode($key, true);

    // Determine which separator makes sense given a URL
    $separator = (strpos($url, '?') === false) ? '?' : '&';

    // Concatenate url with expected query parameters Expires and KeyName
    $url = "{$url}{$separator}Expires={$expiration_time}&KeyName={$keyName}";

    // Sign the url using the key and encode the signature using base64
    $signature = hash_hmac('sha1', $url, $decoded_key, true);
    $encoded_signature = base64_encode($signature);

    // Concatenate the URL and encoded signature
    return "{$url}&Signature={$encoded_signature}";
}
// [END signed_url]

// Example function call (In production store the key safely with other secrets)
$base64_key = '4RfgBoqmotRolLCtU-82Ew=='; // head -c 16 /dev/urandom | base64 | tr +/ -_
$signed_url = signUrl('https://example.com/foo', 'MY-KEY', $base64_key, time() + 1800);
echo $signed_url."\n";
