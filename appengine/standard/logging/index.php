<?php

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
