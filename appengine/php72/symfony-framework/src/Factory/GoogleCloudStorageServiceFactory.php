<?php

namespace App\Factory;

use Google_Client;
use \Google_Service_Storage;

class GoogleCloudStorageServiceFactory
{
    /** @var string */
    private $scope;

    /** @var string */
    private $keyLocation;

    /**
     * GoogleCloudStorageServiceFactory constructor.
     * @param $scope
     * @param $keyLocation
     */
    public function __construct($scope, $keyLocation)
    {
        $this->scope = $scope;
        $this->keyLocation = $keyLocation;
    }

    /**
     * @return Google_Service_Storage
     */
    public function createService() {
        $client = new Google_Client();
        $client->setAuthConfig($this->keyLocation);
        $client->setScopes([$this->scope]);
        return new Google_Service_Storage($client);
    }
}
