<?php
/**
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

// Install composer dependencies with "composer install"
// @see http://getcomposer.org for more information.
require __DIR__ . '/vendor/autoload.php';

use Google\Auth\Credentials\GCECredentials;

/**
 * This sample shows various ways to access the Metadata server, which provides
 * information about your running instance.
 * @see https://cloud.google.com/compute/docs/storing-retrieving-metadata
 */

# [START gae_metadata]
/**
 * Requests a key from the Metadata server using the Google Cloud SDK. Install
 * the Google Cloud SDK by running "composer install google/cloud"
 *
 * @param $metadataKey the key for the metadata server
 */
function request_metadata_using_google_cloud($metadataKey)
{
    $metadata = new Google\Cloud\Core\Compute\Metadata();
    $metadataValue = $metadata->get($metadataKey);

    return $metadataValue;
}

/**
 * Requests a key from the Metadata server using cURL.
 *
 * @param $metadataKey the key for the metadata server
 */
function request_metadata_using_curl($metadataKey)
{
    $url = 'http://metadata/computeMetadata/v1/' . $metadataKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Metadata-Flavor: Google'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    return curl_exec($ch);
}
# [END gae_metadata]

function print_metadata_paths($root = '')
{
    $keys = request_metadata_using_google_cloud($root);
    $html = '<ul>';
    foreach (explode("\n", trim($keys)) as $key) {
        $path = $root . $key;
        $html .= '<li>';
        if (substr($key, -1) == '/') {
            $html .= sprintf('<strong>%s</strong><br />', $key);
            $html .= print_metadata_paths($path);
        } else {
            $html .= sprintf('<a href="/?path=%s">%s</a>', urlencode($path), $key);
        }
        $html .= '</li>';
    }
    return $html . '</ul>';
}

if (!GCECredentials::onGce()) {
    exit('The metadata server can only be reached when running on App Engine.');
}
?>
<html>
    <body>
        <h2>Call the Metadata server</h2>
        <?php if (isset($_GET['path'])): ?>
        <h3>Metadata for <code><?= $_GET['path'] ?></code>:</h3>
        <ul>
            <?php if ('/token' == substr($_GET['path'], -6)): ?>
            <li><em>the metadata value requested contains sensitive information and so will not be displayed here</em></li>
            <?php else: ?>
            <li>With Google Cloud: <pre><?= request_metadata_using_google_cloud($_GET['path']) ?></pre></li>
            <li>With cURL: <pre><?= request_metadata_using_curl($_GET['path']) ?></pre></li>
            <?php endif ?>
        </ul>
        <?php endif ?>
        <h3>All metadata keys:</h3>
        <?= print_metadata_paths() ?>
    </body>
</html>
