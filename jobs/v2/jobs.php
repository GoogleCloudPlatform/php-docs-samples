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

namespace Google\Cloud\Samples\Jobs;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application('Google Cloud Job Discovery');

$application->add(new AutoCompleteSample());
$application->add(new BasicCompanySample());
$application->add(new BasicJobSample());
$application->add(new BatchOperationSample());
$application->add(new CommuteSearchSample());
$application->add(new CustomAttributeSample());
$application->add(new EmailAlertSearchSample());
$application->add(new FeaturedJobsSearchSample());
$application->add(new HistogramSample());
$application->add(new GeneralSearchSample());
$application->add(new LocationSearchSample());

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
