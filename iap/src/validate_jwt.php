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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/auth/README.md
 */

# [START validate_jwt]
namespace Google\Cloud\Samples\Iap;

# Imports OAuth Guzzle HTTP libraries.
use Google\Auth\OAuth2;
use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use Google\Auth\Middleware\ScopedAccessTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Firebase\JWT\JWT;
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
 * @return (user_id, user_email, error_str).
 */
function validate_jwt_from_app_engine($iap_jwt, $cloud_project_number, $cloud_project_id)
{
    $expected_audience = sprintf('/projects/%s/apps/%s', $cloud_project_number, $cloud_project_id);
    return validate_jwt($iap_jwt, $expected_audience);
}

function validate_jwt($iap_jwt, $expected_audience)
{
    // Validate the algorithm and kid headers. Also fetch the public key using the kid.
    $token = (new Parser())->parse((string) $iap_jwt); // Parses from a string
    $algorithm = $token->getHeader('alg');
    assert($algorithm =='ES256');
    $kid = $token->getHeader('kid');
    var_dump($kid);
    $client = new Client(['base_uri' => 'https://www.gstatic.com/']);
    $response = $client->request('GET', 'iap/verify/public_key');
    $body_content = json_decode((string) $response->getBody());
    $public_key = $body_content->$kid;
    var_dump($public_key);

    // Validate token by checking issuer and audience fields. The JWT library automatically checks the time constraints.
    $data = new ValidationData();
    $data->setIssuer('https://cloud.google.com/iap');
    $data->setAudience($expected_audience);
    var_dump($token->validate($data));

    // Verify the signature using a JWT library
    $signer = new Sha256();
    var_dump($token->verify($signer, $public_key));

    // Print out relevant fields
    var_dump($token->getClaim('sub'));
    var_dump($token->getClaim('email'));

    /* FIREBASE JWT CODE
    // Get the kid from the IAP JWT
    $jwt_pieces = explode('.', $iap_jwt);
    $jwt_header = base64_decode($jwt_pieces[0]);
    $jwt_header_json = json_decode($jwt_header, true);
    $keyId = $jwt_header_json['kid'];

    // Get the payload from the IAP JWT
    $jwt_payload = base64_decode($jwt_pieces[1]);
    $jwt_payload_json = json_decode($jwt_payload, true);
    var_dump($jwt_payload_json);

    // Fetch the public key from this URL: https://www.gstatic.com/iap/verify/public_key
    $client = new Client(['base_uri' => 'https://www.gstatic.com/']);
    $response = $client->request('GET', 'iap/verify/public_key');
    $body_content = json_decode((string) $response->getBody());
    $key = $body_content->$keyId;
    var_dump($key);

    // Decode the JWT using the public key
    JWT::$supported_algs['ES256'] = ['hash_hmac', 'SHA256'];
    $decoded = JWT::decode($iap_jwt, $key, array('ES256'));
    $decoded_array = (array) $decoded;
    var_dump($decoded_array);
    */
}
# [END validate_jwt]
