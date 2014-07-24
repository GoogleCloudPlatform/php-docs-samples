<?php
/**
 * Copyright 2012 Google Inc.
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
 * Follow the instructions on https://github.com/google/google-api-php-client
 * to download, extract and include the Google APIs client library for PHP into
 * your project.
 */
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_StorageService.php';

session_start();

/**
 * Visit https://console.developers.google.com to generate your
 * oauth2_client_id, oauth2_client_secret, and to register your
 * oauth2_redirect_uri.
 */
$client = new Google_Client();
$client->setApplicationName("Google Cloud Storage PHP Starter Application");
$client->setClientId('YOUR_CLIENT_ID');
$client->setClientSecret('YOUR_CLIENT_SECRET');
$client->setRedirectUri('YOUR_REDIRECT_URI');
$client->setDeveloperKey('YOUR_API_KEY');
$client->setScopes('https://www.googleapis.com/auth/devstorage.full_control');
$storageService = new Google_StorageService($client);

/**
 * Constants for sample request parameters.
 */
define('API_VERSION', 'v1');
define('DEFAULT_PROJECT', 'YOUR_DEFAULT_PROJECT_ID');
define('DEFAULT_BUCKET', 'YOUR_DEFAULT_BUCKET_NAME');
define('DEFAULT_OBJECT', 'YOUR_DEFAULT_OBJECT_NAME');

/**
 * Generates the markup for a specific Google Cloud Storage API request.
 * @param string $apiRequestName The name of the API request to process.
 * @param string $apiResponse The API response to process.
 * @return string Markup for the specific Google Cloud Storage API request.
 */
function generateMarkup($apiRequestName, $apiResponse) {
  $apiRequestMarkup = '';
  $apiRequestMarkup .= "<header><h2>" . $apiRequestName . "</h2></header>";

  if ($apiResponse['items'] == '' ) {
    $apiRequestMarkup .= "<pre>";
    $apiRequestMarkup .= print_r(json_decode(json_encode($apiResponse), true),
      true);
    $apiRequestMarkup .= "</pre>";
  } else {
    foreach($apiResponse['items'] as $response) {
      $apiRequestMarkup .= "<pre>";
      $apiRequestMarkup .= print_r(json_decode(json_encode($response), true),
        true);
      $apiRequestMarkup .= "</pre>";
    }
  }

  return $apiRequestMarkup;
}

/**
 * Clear access token whenever a logout is requested.
 */
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}

/**
 * Authenticate and set client access token.
 */
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

/**
 * Set client access token.
 */
if (isset($_SESSION['access_token'])) {
  $client->setAccessToken($_SESSION['access_token']);
}

/**
 * If all authentication has been successfully completed, make Google
 * Cloud Storage API requests.
 */
if ($client->getAccessToken()) {
  /**
   * Google Cloud Storage API request to retrieve the list of buckets in your
   * Google Cloud Storage project.
   */
  $buckets = $storageService->buckets->listBuckets(DEFAULT_PROJECT);
  $listBucketsMarkup = generateMarkup('List Buckets', $buckets);

  /**
   * Google Cloud Storage API request to retrieve the list of objects in your
   * Google Cloud Storage bucket.
   */
  $objects = $storageService->objects->listObjects(DEFAULT_BUCKET);
  $listObjectsMarkup = generateMarkup('List Objects', $objects);

  /**
   * Google Cloud Storage API request to retrieve the list of Access Control
   * Lists on your Google Cloud Storage buckets.
   */
  $bucketsAccessControls = $storageService->bucketAccessControls->
    listBucketAccessControls(DEFAULT_BUCKET);
  $listBucketsAccessControlsMarkup = generateMarkup(
    'List Buckets Access Controls', $bucketsAccessControls);

  /**
   * Google Cloud Storage API request to retrieve the list of Access Control
   * Lists on your Google Cloud Storage objects.
   */
  $objectsAccessControls = $storageService->objectAccessControls->
    listObjectAccessControls(DEFAULT_BUCKET, DEFAULT_OBJECT);
  $listObjectsAccessControlsMarkup = generateMarkup(
    'List Objects Access Controls', $objectsAccessControls);

  /**
   * Google Cloud Storage API request to retrieve a bucket from your
   * Google Cloud Storage project.
   */
  $bucket = $storageService->buckets->get(DEFAULT_BUCKET);
  $getBucketMarkup = generateMarkup('Get Bucket', $bucket);

  // The access token may have been updated lazily.
  $_SESSION['access_token'] = $client->getAccessToken();
} else {
  $authUrl = $client->createAuthUrl();
}
?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
  </head>
  <body>
    <header><h1>Google Cloud Storage Sample App</h1></header>
    <div class="main-content">
      <?php if(isset($listBucketsMarkup)): ?>
        <div id="listBuckets">
          <?php print $listBucketsMarkup ?>
        </div>
      <?php endif ?>

      <?php if(isset($listObjectsMarkup)): ?>
        <div id="listObjects">
          <?php print $listObjectsMarkup ?>
        </div>
      <?php endif ?>

      <?php if(isset($listBucketsAccessControlsMarkup)): ?>
        <div id="listBucketsAccessControls">
          <?php print $listBucketsAccessControlsMarkup ?>
        </div>
      <?php endif ?>

      <?php if(isset($listObjectsAccessControlsMarkup)): ?>
        <div id="listObjectsAccessControls">
          <?php print $listObjectsAccessControlsMarkup ?>
        </div>
      <?php endif ?>

      <?php if(isset($getBucketMarkup)): ?>
        <div id="getBucket">
          <?php print $getBucketMarkup ?>
        </div>
      <?php endif ?>

      <?php
        if(isset($authUrl)) {
          print "<a class='login' href='$authUrl'>Authorize Me!</a>";
        } else {
          print "<a class='logout' href='?logout'>Logout</a>";
        }
      ?>
    </div>
  </body>
</html>
