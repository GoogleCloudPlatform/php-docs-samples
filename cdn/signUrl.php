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
 * Decodes base64url (RFC4648 Section 5) string
 *
 * @param string $input base64url encoded string
 *
 * @return string
 */
function base64url_decode($input)
{
    $input .= str_repeat('=', (4 - strlen($input) % 4) % 4);
    return base64_decode(strtr($input, '-_', '+/'));
}

/**
* Encodes a string with base64url (RFC4648 Section 5)
* Keeps the '=' padding by default.
*
* @param string $input   String to be encoded
* @param bool   $padding Keep the '=' padding
*
* @return string
*/
function base64url_encode($input, $padding = true)
{
    $output = strtr(base64_encode($input), '+/', '-_');
    return ($padding) ? $output : str_replace('=', '',  $output);
}

/**
 * Creates signed URL for Google Cloud CDN
 * Details about order of operations: https://cloud.google.com/cdn/docs/using-signed-urls#creating_signed_urls
 *
 * Example function invocation (In production store the key safely with other secrets):
 *
 *     <?php
 *     $base64UrlKey = 'wpLL7f4VB9RNe_WI0BBGmA=='; // head -c 16 /dev/urandom | base64 | tr +/ -_
 *     $signedUrl = sign_url('https://example.com/foo', 'my-key', $base64UrlKey, time() + 1800);
 *     echo $signedUrl;
 *     ?>
 *
 * @param string $url             URL of the endpoint served by Cloud CDN
 * @param string $keyName         Name of the signing key added to the Google Cloud Storage bucket or service
 * @param string $base64UrlKey    Signing key as base64url (RFC4648 Section 5) encoded string
 * @param int    $expirationTime  Expiration time as a UNIX timestamp (GMT, e.g. time())
 *
 * @return string
 */
function sign_url($url, $keyName, $base64UrlKey, $expirationTime)
{
    // Decode the key
    $decodedKey = base64url_decode($base64UrlKey, true);

    // Determine which separator makes sense given a URL
    $separator = (strpos($url, '?') === false) ? '?' : '&';

    // Concatenate url with expected query parameters Expires and KeyName
    $url = "{$url}{$separator}Expires={$expirationTime}&KeyName={$keyName}";

    // Sign the url using the key and encode the signature using base64url
    $signature = hash_hmac('sha1', $url, $decodedKey, true);
    $encodedSignature = base64url_encode($signature);

    // Concatenate the URL and encoded signature
    return "{$url}&Signature={$encodedSignature}";
}
// [END signed_url]
