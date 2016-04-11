<?php
/**
 * Copyright 2016 Google Inc.
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

namespace Google\Cloud\TestUtils;

/**
 * Class GaeApp
 * @package Google\Cloud\TestUtils
 *
 * A class representing App Engine application.
 */
class GaeApp
{
    private $project;
    private $version;
    private $port;
    private $deployed;
    private $process;
    private $dir;

    const DEFAULT_RETRY = 3;
    const GCLOUD_APP = 'preview app';
    const DEFAULT_PORT = 8080;

    private function errorLog($message)
    {
        fwrite(STDERR, $message . "\n");
    }

    private function execWithRetry($cmd, $retry = self::DEFAULT_RETRY)
    {
        for ($i = 0; $i <= $retry; $i++) {
            exec($cmd, $output, $ret);
            if ($ret === 0) {
                return true;
            } else if ($i <= $retry) {
                $this->errorLog('Retrying the command: ' . $cmd);
            }
        }
        return false;
    }

    /**
     * Constructor of GaeApp.
     *
     * @param string $project
     * @param string $version
     * @param string|null $dir optional
     * @param int $port optional
     */
    public function __construct(
        $project,
        $version,
        $dir = null,
        $port = self::DEFAULT_PORT
    ) {
        $this->project = $project;
        if ($version === null) {
            $version = uniqid('gaeapp-');
        }
        if ($dir === null) {
            $dir = getcwd();
        }
        $this->version = $version;
        $this->deployed = false;
        $this->dir = $dir;
        $this->port = $port;
    }

    /**
     * Deploys the app to the Google Cloud Platform.
     *
     * @param string $target yaml files for deployments
     * @param bool $promote optional true if you want to promote the new app
     * @param int $retry optional number of retries upon failure
     *
     * @return bool true if deployment suceeds, false upon failure
     */
    public function deploy(
        $target = 'app.yaml',
        $promote = false,
        $retry = self::DEFAULT_RETRY
    ) {
        if ($this->deployed) {
            $this->errorLog('The app has been already deployed.');
            return false;
        }
        $orgDir = getcwd();
        $cmd = "gcloud -q " . self::GCLOUD_APP . " deploy "
            . "--project " . $this->project . " "
            . "--version " . $this->version . " ";
        if ($promote) {
            $cmd .= "--promote ";
        } else {
            $cmd .= "--no-promote ";
        }
        $cmd .= $target;
        $ret = $this->execWithRetry($cmd, $retry);
        chdir($orgDir);
        if ($ret) {
            $this->deployed = true;
        }
        return $ret;
    }

    /**
     * Deletes the deployed app.
     *
     * @param string $module
     * @param int $retry optional number of retries upon failure
     *
     * @return bool true if the app is succesfully deleted, otherwise false
     */
    public function delete(
        $module = 'default',
        $retry = self::DEFAULT_RETRY
    ) {
        $cmd = "gcloud -q " . self::GCLOUD_APP . " modules delete $module "
            . "--version " . $this->version . " --project " . $this->project;
        $ret = $this->execWithRetry($cmd, $retry);
        return $ret;
    }

    /**
     * Returns the base URL of the deployed app.
     *
     * @return mixed returns the base URL of the deployed app, or false when
     *     the app is not deployed.
     */
    public function getBaseUrl($module = 'default')
    {
        if (! $this->deployed) {
            $this->errorLog('The app has not been deployed.');
            return false;
        }
        if ($module === 'default') {
            $url = sprintf(
                'https://%s-dot-%s.appspot.com',
                $this->version,
                $this->project
            );
        } else {
            $url = sprintf(
                'https://%s-dot-%s-dot-%s.appspot.com',
                $this->version,
                $module,
                $this->project
            );
        }
        return $url;
    }
}
