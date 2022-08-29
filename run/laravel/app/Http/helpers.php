<?php

use Google\Auth\Credentials\GCECredentials;

if (!function_exists('request_metadata')) {
    /**
     * Get information from the metadata server
     * https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/appengine/standard/metadata/index.php
     *
     * @return string
     */

    // [START cloudrun_laravel_get_metadata]
    function request_metadata($metadataKey)
    {
        if (!GCECredentials::onGce()) {
            return 'Unknown';
        }

        $metadata = new Google\Cloud\Core\Compute\Metadata();
        $metadataValue = $metadata->get($metadataKey);

        return $metadataValue;
    }
    // [END cloudrun_laravel_get_metadata]
}
