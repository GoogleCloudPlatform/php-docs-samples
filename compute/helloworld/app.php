<?php
/*
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
 * Follow the instructions on https://code.google/com/p/google-api-php-client
 * to download, extract and include the Google APIs client library for PHP into
 * your project.
 */
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_ComputeService.php';

session_start();

/**
 * Visit https://code.google.com/apis/console to generate your
 * oauth2_client_id, oauth2_client_secret, and to register your
 * oauth2_redirect_uri.
 */
$client = new Google_Client();
$client->setApplicationName("Google Compute Engine PHP Starter Application");
$client->setClientId('YOUR_CLIENT_ID');
$client->setClientSecret('YOUR_CLIENT_SECRET');
$client->setRedirectUri('YOUR_REDIRECT_URI');
$computeService = new Google_ComputeService($client);

/**
 * The name of your Google Compute Engine Project.
 */
$project = 'YOUR_GOOGLE_COMPUTE_ENGINE_PROJECT';

/**
 * Constants for sample request parameters.
 */
define('API_VERSION', 'v1beta14');
define('BASE_URL', 'https://www.googleapis.com/compute/' .
  API_VERSION . '/projects/');
define('GOOGLE_PROJECT', 'google');
define('DEFAULT_PROJECT', $project);
define('DEFAULT_NAME', 'new-node');
define('DEFAULT_NAME_WITH_METADATA', 'new-node-with-metadata');
define('DEFAULT_MACHINE_TYPE', BASE_URL . DEFAULT_PROJECT .
  '/global/machineTypes/n1-standard-1');
define('DEFAULT_ZONE_NAME', 'us-central1-a');
define('DEFAULT_ZONE', BASE_URL . DEFAULT_PROJECT . '/zones/' . DEFAULT_ZONE_NAME);
define('DEFAULT_IMAGE', BASE_URL . GOOGLE_PROJECT .
  '/global/images/gcel-12-04-v20130104');
define('DEFAULT_NETWORK', BASE_URL . DEFAULT_PROJECT .
  '/global/networks/default');

/**
 * Generates the markup for a specific Google Compute Engine API request.
 * @param string $apiRequestName The name of the API request to process.
 * @param string $apiResponse The API response to process.
 * @return string Markup for the specific Google Compute Engine API request.
 */
function generateMarkup($apiRequestName, $apiResponse)
{
    $apiRequestMarkup = '';
    $apiRequestMarkup .= "<header><h2>" . $apiRequestName . "</h2></header>";

    if ($apiResponse['items'] == '') {
        $apiRequestMarkup .= "<pre>";
        $apiRequestMarkup .= print_r(json_decode(json_encode($apiResponse), true), true);
        $apiRequestMarkup .= "</pre>";
    } else {
        foreach ($apiResponse['items'] as $response) {
            $apiRequestMarkup .= "<pre>";
            $apiRequestMarkup .= print_r(json_decode(json_encode($response), true), true);
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
 * If all authentication has been successfully completed, make Google Compute
 * Engine API requests.
 */
if ($client->getAccessToken()) {
    /**
   * Google Compute Engine API request to retrieve the list of instances in your
   * Google Compute Engine project.
   */
    $instances = $computeService->instances->listInstances(
        DEFAULT_PROJECT,
        DEFAULT_ZONE_NAME
    );

    $instancesListMarkup = generateMarkup(
        'List Instances',
        $instances
    );

    /**
     * Google Compute Engine API request to retrieve the list of all data center
     * locations associated with your Google Compute Engine project.
     */
    $zones = $computeService->zones->listZones(DEFAULT_PROJECT);
    $zonesListMarkup = generateMarkup('List Zones', $zones);

    /**
     * Google Compute Engine API request to retrieve the list of all machine types
     * associated with your Google Compute Engine project.
     */
    $machineTypes = $computeService->machineTypes->listMachineTypes(DEFAULT_PROJECT);
    $machineTypesListMarkup = generateMarkup(
        'List Machine Types',
        $machineTypes
    );

    /**
     * Google Compute Engine API request to retrieve the list of all image types
     * associated with your Google Compute Engine project.
     */
    $images = $computeService->images->listImages(GOOGLE_PROJECT);
    $imagesListMarkup = generateMarkup('List Images', $images);

    /**
     * Google Compute Engine API request to retrieve the list of all firewalls
     * associated with your Google Compute Engine project.
     */
    $firewalls = $computeService->firewalls->listFirewalls(DEFAULT_PROJECT);
    $firewallsListMarkup = generateMarkup('List Firewalls', $firewalls);

    /**
     * Google Compute Engine API request to retrieve the list of all networks
     * associated with your Google Compute Engine project.
     */
    $networks = $computeService->networks->listNetworks(DEFAULT_PROJECT);
    $networksListMarkup = generateMarkup('List Networks', $networks);
    ;

    /**
     * Google Compute Engine API request to insert a new instance into your Google
     * Compute Engine project.
     */
    $name = DEFAULT_NAME;
    $machineType = DEFAULT_MACHINE_TYPE;
    $zone = DEFAULT_ZONE_NAME;
    $image = DEFAULT_IMAGE;

    $googleNetworkInterfaceObj = new Google_NetworkInterface();
    $network = DEFAULT_NETWORK;
    $googleNetworkInterfaceObj->setNetwork($network);

    $new_instance = new Google_Instance();
    $new_instance->setName($name);
    $new_instance->setImage($image);
    $new_instance->setMachineType($machineType);
    $new_instance->setNetworkInterfaces(array($googleNetworkInterfaceObj));

    $insertInstance = $computeService->instances->insert(DEFAULT_PROJECT,
    $zone, $new_instance);
    $insertInstanceMarkup = generateMarkup('Insert Instance', $insertInstance);

    /**
     * Google Compute Engine API request to insert a new instance (with metadata)
     * into your Google Compute Engine project.
     */
    $name = DEFAULT_NAME_WITH_METADATA;
    $machineType = DEFAULT_MACHINE_TYPE;
    $zone = DEFAULT_ZONE_NAME;
    $image = DEFAULT_IMAGE;

    $googleNetworkInterfaceObj = new Google_NetworkInterface();
    $network = DEFAULT_NETWORK;
    $googleNetworkInterfaceObj->setNetwork($network);

    $metadataItemsObj = new Google_MetadataItems();
    $metadataItemsObj->setKey('startup-script');
    $metadataItemsObj->setValue('apt-get install apache2');

    $metadata = new Google_Metadata();
    $metadata->setItems(array($metadataItemsObj));

    $new_instance = new Google_Instance();
    $new_instance->setName($name);
    $new_instance->setImage($image);
    $new_instance->setMachineType($machineType);
    $new_instance->setNetworkInterfaces(array($googleNetworkInterfaceObj));
    $new_instance->setMetadata($metadata);

    $insertInstanceWithMetadata = $computeService->instances->insert(
        DEFAULT_PROJECT,
        $zone,
        $new_instance
    );

    $insertInstanceWithMetadataMarkup = generateMarkup(
        'Insert Instance With Metadata',
        $insertInstanceWithMetadata
    );

    /**
     * Google Compute Engine API request to get an instance matching the outlined
     * parameters from your Google Compute Engine project.
     */
    $getInstance = $computeService->instances->get(
        DEFAULT_PROJECT,
        DEFAULT_ZONE_NAME,
        DEFAULT_NAME
    );

    $getInstanceMarkup = generateMarkup('Get Instance', $getInstance);

    /**
     * Google Compute Engine API request to get an instance matching the outlined
     * parameters from your Google Compute Engine project.
     */
    $getInstanceWithMetadata = $computeService->instances->get(
        DEFAULT_PROJECT,
        DEFAULT_ZONE_NAME,
        DEFAULT_NAME_WITH_METADATA
    );

    $getInstanceWithMetadataMarkup = generateMarkup(
        'Get Instance With Metadata',
        $getInstanceWithMetadata
    );

    /**
     * Google Compute Engine API request to delete an instance matching the
     * outlined parameters from your Google Compute Engine project.
     */
    $deleteInstance = $computeService->instances->delete(
        DEFAULT_PROJECT,
        DEFAULT_ZONE_NAME,
        DEFAULT_NAME
    );

    $deleteInstanceMarkup = generateMarkup('Delete Instance', $deleteInstance);

    /**
     * Google Compute Engine API request to delete an instance matching the
     * outlined parameters from your Google Compute Engine project.
     */
    $deleteInstanceWithMetadata = $computeService->instances->delete(
        DEFAULT_PROJECT,
        DEFAULT_ZONE_NAME,
        DEFAULT_NAME_WITH_METADATA
    );

    $deleteInstanceWithMetadataMarkup = generateMarkup(
        'Delete Instance With Metadata',
        $deleteInstanceWithMetadata
    );

    /**
     * Google Compute Engine API request to retrieve the list of all global
     * operations associated with your Google Compute Engine project.
     */
    $globalOperations = $computeService->globalOperations->listGlobalOperations(
        DEFAULT_PROJECT
    );

    $operationsListMarkup = generateMarkup(
        'List Global Operations',
        $globalOperations
    );

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
    <header><h1>Google Compute Engine Sample App</h1></header>
    <div class="main-content">
      <?php if (isset($instancesListMarkup)): ?>
        <div id="listInstances"><?php print $instancesListMarkup ?></div>
      <?php endif ?>

      <?php if (isset($zonesListMarkup)): ?>
        <div id="listZones"><?php print $zonesListMarkup ?></div>
      <?php endif ?>

      <?php if (isset($machineTypesListMarkup)): ?>
        <div id="listMachineTypes"><?php print $machineTypesListMarkup ?></div>
      <?php endif ?>

      <?php if (isset($imagesListMarkup)): ?>
        <div id="listImages"><?php print $imagesListMarkup ?></div>
      <?php endif ?>

      <?php if (isset($firewallsListMarkup)): ?>
        <div id="listFirewalls"><?php print $firewallsListMarkup ?></div>
      <?php endif ?>

      <?php if (isset($networksListMarkup)): ?>
        <div id="listNetworks"><?php print $networksListMarkup ?></div>
      <?php endif ?>

      <?php if (isset($getInstanceWithMetadataMarkup)): ?>
        <div id="getInstanceWithMetadata">
          <?php print $getInstanceWithMetadataMarkup ?>
        </div>
      <?php endif ?>

      <?php if (isset($getInstanceMarkup)): ?>
        <div id="getInstance"><?php print $getInstanceMarkup ?></div>
      <?php endif ?>

      <?php if (isset($deleteInstanceMarkup)): ?>
        <div id="deleteInstance"><?php print $deleteInstanceMarkup ?></div>
      <?php endif ?>

      <?php if (isset($deleteInstanceWithMetadataMarkup)): ?>
        <div id="deleteInstanceWithMetadata">
          <?php print $deleteInstanceWithMetadataMarkup ?>
        </div>
      <?php endif ?>

      <?php if (isset($insertInstanceMarkup)): ?>
        <div id="insertInstance"><?php print $insertInstanceMarkup ?></div>
      <?php endif ?>

      <?php if (isset($insertInstanceWithMetadataMarkup)): ?>
        <div id="insertInstanceWithMetadata">
          <?php print $insertInstanceWithMetadataMarkup?>
        </div>
      <?php endif ?>

      <?php if (isset($operationsListMarkup)): ?>
        <div id="listGlobalOperations"><?php print $operationsListMarkup ?></div>
      <?php endif ?>

      <?php
        if (isset($authUrl)) {
            print "<a class='login' href='$authUrl'>Connect Me!</a>";
        } else {
            print "<a class='logout' href='?logout'>Logout</a>";
        }
      ?>
    </div>
  </body>
</html>
