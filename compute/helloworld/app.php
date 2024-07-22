<?php
/*
 * Copyright 2021 Google Inc.
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

require_once 'vendor/autoload.php';

use Google\Cloud\Compute\V1\Client\DisksClient;
use Google\Cloud\Compute\V1\Client\FirewallsClient;
use Google\Cloud\Compute\V1\Client\GlobalOperationsClient;
use Google\Cloud\Compute\V1\Client\ImagesClient;
use Google\Cloud\Compute\V1\Client\InstancesClient;
use Google\Cloud\Compute\V1\Client\MachineTypesClient;
use Google\Cloud\Compute\V1\Client\NetworksClient;
use Google\Cloud\Compute\V1\Client\ZonesClient;
use Google\Cloud\Compute\V1\ListDisksRequest;
use Google\Cloud\Compute\V1\ListFirewallsRequest;
use Google\Cloud\Compute\V1\ListGlobalOperationsRequest;
use Google\Cloud\Compute\V1\ListImagesRequest;
use Google\Cloud\Compute\V1\ListInstancesRequest;
use Google\Cloud\Compute\V1\ListMachineTypesRequest;
use Google\Cloud\Compute\V1\ListNetworksRequest;
use Google\Cloud\Compute\V1\ListZonesRequest;
use Google\Protobuf\Internal\Message;

/**
 * Set these variables to your project and zone.
 */
$projectId = 'php-docs-samples-kokoro';
$zoneName = 'us-central1-f';

// Instantiate clients for calling the Compute API.
$instancesClient = new InstancesClient();
$zonesClient = new ZonesClient();
$disksClient = new DisksClient();
$machineTypesClient = new MachineTypesClient();
$imagesClient = new ImagesClient();
$firewallsClient = new FirewallsClient();
$networksClient = new NetworksClient();
$globalOperationsClient = new GlobalOperationsClient();

/**
 * Helper function to pretty-print a Protobuf message.
 */
function print_message(Message $message)
{
    return json_encode(
        json_decode($message->serializeToJsonString(), true),
        JSON_PRETTY_PRINT
    );
}

$request = (new ListInstancesRequest())
    ->setProject($projectId)
    ->setZone($zoneName);
$request2 = (new ListZonesRequest())
    ->setProject($projectId);
$request3 = (new ListDisksRequest())
    ->setProject($projectId)
    ->setZone($zoneName);
$request4 = (new ListMachineTypesRequest())
    ->setProject($projectId)
    ->setZone($zoneName);
$request5 = (new ListImagesRequest())
    ->setProject($projectId);
$request6 = (new ListFirewallsRequest())
    ->setProject($projectId);
$request7 = (new ListNetworksRequest())
    ->setProject($projectId);
$request8 = (new ListGlobalOperationsRequest())
    ->setProject($projectId);
?>
<!doctype html>
<html>
    <head><meta charset="utf-8"></head>
    <body>
        <header><h1>Google Cloud Compute Sample App</h1></header>
        <div class="main-content">
            <h2 class="collapsible">List Instances</h2>
            <div id="listInstances" class="collapsible-content">
                <?php foreach ($instancesClient->list($request) as $instance): ?>
                    <pre><?= print_message($instance) ?></pre>
                <?php endforeach ?>
            </div>

            <h2 class="collapsible">List Zones</h2>
            <div id="listZones" class="collapsible-content">
                <?php foreach ($zonesClient->list($request2) as $zone): ?>
                    <pre><?= print_message($zone) ?></pre>
                <?php endforeach ?>
            </div>

            <h2 class="collapsible">List Disks</h2>
            <div id="listDisks" class="collapsible-content">
                <?php foreach ($disksClient->list($request3) as $disk): ?>
                    <pre><?= print_message($disk) ?></pre>
                <?php endforeach ?>
            </div>

            <h2 class="collapsible">List Machine Types</h2>
            <div id="listMachineTypes" class="collapsible-content">
                <?php foreach ($machineTypesClient->list($request4) as $machineType): ?>
                    <pre><?= print_message($machineType) ?></pre>
                <?php endforeach ?>
            </div>

            <h2 class="collapsible">List Images</h2>
            <div id="listImages" class="collapsible-content">
                <?php foreach ($imagesClient->list($request5) as $image): ?>
                    <pre><?= print_message($image) ?></pre>
                <?php endforeach ?>
            </div>

            <h2 class="collapsible">List Firewalls</h2>
            <div id="listFirewalls" class="collapsible-content">
                <?php foreach ($firewalls = $firewallsClient->list($request6) as $firewall): ?>
                    <pre><?= print_message($firewall) ?></pre>
                <?php endforeach ?>
            </div>

            <h2 class="collapsible">List Networks</h2>
            <div id="listNetworks" class="collapsible-content">
                <?php foreach ($networksClient->list($request7) as $network): ?>
                    <pre><?= print_message($network) ?></pre>
                <?php endforeach ?>
            </div>

            <h2 class="collapsible">List Operations</h2>
            <div id="listGlobalOperations" class="collapsible-content">
                <?php foreach ($globalOperationsClient->list($request8) as $operation): ?>
                    <pre><?= print_message($operation) ?></pre>
                <?php endforeach ?>
            </div>
        </div>
    </body>
</html>

<style>
.collapsible {
  background-color: #eee;
  color: #444;
  cursor: pointer;
  padding: 18px;
  width: 100%;
  border: none;
  text-align: left;
  outline: none;
  font-size: 15px;
}

.active, .collapsible:hover {
  background-color: #ccc;
}

.collapsible-content {
  padding: 0 18px;
  display: none;
  overflow: hidden;
  background-color: #f1f1f1;
}
</style>

<script>
  var coll = document.getElementsByClassName("collapsible");
  var i;

  for (i = 0; i < coll.length; i++) {
    coll[i].addEventListener("click", function() {
      this.classList.toggle("active");
      var content = this.nextElementSibling;
      if (content.style.display === "block") {
        content.style.display = "none";
      } else {
        content.style.display = "block";
      }
    });
  }
</script>
