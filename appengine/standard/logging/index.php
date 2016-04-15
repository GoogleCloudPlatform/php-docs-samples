<?php
/**
 * Copyright 2015 Google Inc.
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

# [START fetch_logs]
use google\appengine\api\log\LogService;

// LogService API usage sample to display application logs for last 24 hours.
$options = [
  // Fetch last 24 hours of log data
  'start_time' => (time() - (24 * 60 * 60)) * 1e6,
  // End time is Now
  'end_time' => time() * 1e6,
  // Include all Application Logs (i.e. your debugging output)
  'include_app_logs' => true,
  // Filter out log records based on severity
  'minimum_log_level' => LogService::LEVEL_INFO,
];

$logs = LogService::fetch($options);
# [END fetch_logs]
?>

<?php if (empty($logs)): ?>
  <h3>No logs!</h3>
<?php endif ?>

<!-- [START display_logs] -->
<?php foreach ($logs as $log): ?>
  <h3>REQUEST LOG</h3>
  <ul>
    <li>IP: <?= $log->getIp() ?></li>
    <li>Status: <?= $log->getStatus() ?></li>
    <li>Method: <?= $log->getMethod() ?></li>
    <li>Resource: <?= $log->getResource() ?></li>
    <li>Date: <?= $log->getEndDateTime()->format('c') ?></li>
    <li>
<?php foreach ($log->getAppLogs() as $app_log): ?>
        <strong>APP LOG</strong>
        <ul>
          <li>Message: <?= $app_log->getMessage() ?></li>
          <li>Date: <?= $app_log->getDateTime()->format('c') ?></li>
        </ul>
<?php endforeach ?>
    </li>
  </ul>
<?php endforeach ?>
<!-- [END display_logs] -->
