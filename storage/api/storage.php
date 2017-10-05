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
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\Storage\BucketAclCommand;
use Google\Cloud\Samples\Storage\BucketDefaultAclCommand;
use Google\Cloud\Samples\Storage\BucketLabelsCommand;
use Google\Cloud\Samples\Storage\BucketsCommand;
use Google\Cloud\Samples\Storage\EncryptionCommand;
use Google\Cloud\Samples\Storage\IamCommand;
use Google\Cloud\Samples\Storage\ObjectAclCommand;
use Google\Cloud\Samples\Storage\ObjectsCommand;
use Google\Cloud\Samples\Storage\RequesterPaysCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new BucketAclCommand());
$application->add(new BucketDefaultAclCommand());
$application->add(new BucketLabelsCommand());
$application->add(new BucketsCommand());
$application->add(new EncryptionCommand());
$application->add(new IamCommand());
$application->add(new ObjectAclCommand());
$application->add(new ObjectsCommand());
$application->add(new RequesterPaysCommand());
$application->run();
