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
use Silex\Application;

# [START gae_flex_metadata]
function get_external_ip_using_google_cloud()
{
    $metadata = new Google\Cloud\Core\Compute\Metadata();
    $externalIp = $metadata->get(
        'instance/network-interfaces/0/access-configs/0/external-ip');

    return $externalIp;
}

function get_external_ip_using_curl()
{
    $url = 'http://metadata.google.internal/computeMetadata/v1/' .
        'instance/network-interfaces/0/access-configs/0/external-ip';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Metadata-Flavor: Google'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    return curl_exec($ch);
}
# [END gae_flex_metadata]

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    if (!$externalIp = get_external_ip_using_google_cloud()) {
        return 'Unable to reach Metadata server - are you running locally?';
    }
    return sprintf('External IP: %s', $externalIp);
});

$app->get('/curl', function () use ($app) {
    if (!$externalIp = get_external_ip_using_curl()) {
        return 'Unable to reach Metadata server - are you running locally?';
    }
    return sprintf('External IP: %s', $externalIp);
});

return $app;
