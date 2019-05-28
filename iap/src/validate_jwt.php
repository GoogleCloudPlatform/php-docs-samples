<?php
/**
 * Copyright 2017 Google Inc.
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
/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/iap/README.md
 */

# [START iap_validate_jwt]
namespace Google\Cloud\Samples\Iap;

# Imports OAuth Guzzle HTTP libraries.
use GuzzleHttp\Client;
# Imports libraries for JWK validation
use SimpleJWT\JWT;
use SimpleJWT\Keys\KeySet;
use SimpleJWT\InvalidTokenException;

/**
 * Validate a JWT passed to your App Engine app by Identity-Aware Proxy.
 *
 * @param string $iap_jwt The contents of the X-Goog-IAP-JWT-Assertion header.
 * @param string $cloud_project_number The project *number* for your Google
 *     Cloud project. This is returned by 'gcloud projects describe $PROJECT_ID',
 *     or in the Project Info card in Cloud Console.
 * @param string $cloud_project Your Google Cloud Project ID.
 *
 * @return (user_id, user_email).
 */
function validate_jwt_from_app_engine($iap_jwt, $cloud_project_number, $cloud_project_id)
{
    $expected_audience = sprintf(
        '/projects/%s/apps/%s',
        $cloud_project_number,
        $cloud_project_id
    );
    return validate_jwt($iap_jwt, $expected_audience);
}

/**
 * Validate a JWT passed to your Compute / Container Engine app by Identity-Aware Proxy.
 *
 * @param string $iap_jwt The contents of the X-Goog-IAP-JWT-Assertion header.
 * @param string $cloud_project_number The project *number* for your Google
 *     Cloud project. This is returned by 'gcloud projects describe $PROJECT_ID',
 *     or in the Project Info card in Cloud Console.
 * @param string $backend_service_id The ID of the backend service used to access the
 *     application. See https://cloud.google.com/iap/docs/signed-headers-howto
 *     for details on how to get this value.
 *
 * @return (user_id, user_email).
 */
function validate_jwt_from_compute_engine($iap_jwt, $cloud_project_number, $backend_service_id)
{
    $expected_audience = sprintf(
        '/projects/%s/global/backendServices/%s',
        $cloud_project_number,
        $backend_service_id
    );
    return validate_jwt($iap_jwt, $expected_audience);
}


function validate_jwt($iap_jwt, $expected_audience)
{
    // get the public key JWK Set object (RFC7517)
    $httpclient = new Client();
    $response = $httpclient->request('GET', 'https://www.gstatic.com/iap/verify/public_key-jwk', []);

    // Create a JWK Key Set from the gstatic URL
    $jwkset = new KeySet();
    $jwkset->load((string) $response->getBody());


    // Validate the signature using the key set and ES256 algorithm.
    try {
        $jwt = JWT::decode($iap_jwt, $jwkset, 'ES256');
    } catch (InvalidTokenException $e) {
        return print("Failed to validate JWT: " . $e->getMessage() . PHP_EOL);
    }
    // Validate token by checking issuer and audience fields.
    assert($jwt->getClaim('iss') == 'https://cloud.google.com/iap');
    assert($jwt->getClaim('aud') == $expected_audience);

    // Return the user identity (subject and user email) if JWT verification is successful.
    return array('sub' => $jwt->getClaim('sub'), 'email' => $jwt->getClaim('email'));
}
# [END iap_validate_jwt]
