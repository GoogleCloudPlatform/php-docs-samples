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

# [START validate_jwt]
namespace Google\Cloud\Samples\Iap;

# Imports OAuth Guzzle HTTP libraries.
use GuzzleHttp\Client;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;

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
    // Validate the algorithm and kid headers. Also fetch the public key using the kid.
    $token = (new Parser())->parse((string) $iap_jwt); // Parses from a string
    $algorithm = $token->getHeader('alg');
    assert($algorithm =='ES256');
    $kid = $token->getHeader('kid');
    $client = new Client(['base_uri' => 'https://www.gstatic.com/']);
    $response = $client->request('GET', 'iap/verify/public_key');
    $body_content = json_decode((string) $response->getBody());
    $public_key = $body_content->$kid;

    // Validate token by checking issuer and audience fields. The JWT library automatically checks the time constraints.
    $data = new ValidationData();
    $data->setIssuer('https://cloud.google.com/iap');
    $data->setAudience($expected_audience);
    assert($token->validate($data));

    // Verify the signature using the JWT library.
    $signer = new Sha256();
    assert($token->verify($signer, $public_key));

    // Return the user identity (subject and user email) if JWT verification is successful.
    return array('sub' => $token->getClaim('sub'), 'email' => $token->getClaim('email'));
}
# [END validate_jwt]
