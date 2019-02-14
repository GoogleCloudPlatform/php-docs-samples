<?php

namespace App;

class Application extends \Illuminate\Foundation\Application
{
    /**
     * Get the path to the bootstrap directory.
     *
     * @param  string  $path Optionally, a path to append to the bootstrap path
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return $_ENV['APP_BOOTSTRAP_PATH'] ?? parent::bootstrapPath($path);
    }

    /**
     * Get the path to the cached services.php file.
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        if (isset($_ENV['APP_BOOTSTRAP_PATH'])) {
            return $this->bootstrapPath().'/services.php';
        } else {
            return parent::getCachedServicesPath();
        }
    }
    /**
     * Get the path to the cached packages.php file.
     *
     * @return string
     */
    public function getCachedPackagesPath()
    {
        if (isset($_ENV['APP_BOOTSTRAP_PATH'])) {
            return $this->bootstrapPath().'/packages.php';
        } else {
            return parent::getCachedPackagesPath();
        }
    }

    /**
     * Get the path to the configuration cache file.
     *
     * @return string
     */
    public function getCachedConfigPath()
    {
        if (isset($_ENV['APP_BOOTSTRAP_PATH'])) {
            return $this->bootstrapPath().'/config.php';
        } else {
            return parent::getCachedConfigPath();
        }
    }

    /**
     * Get the path to the routes cache file.
     *
     * @return string
     */
    public function getCachedRoutesPath()
    {
        if (isset($_ENV['APP_BOOTSTRAP_PATH'])) {
            return $this->bootstrapPath().'/routes.php';
        } else {
            return parent::getCachedRoutesPath();
        }
    }
}
