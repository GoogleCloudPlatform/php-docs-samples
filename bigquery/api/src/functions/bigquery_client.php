<?php

namespace Google\Cloud\Samples\BigQuery;

/**
 * This file is to be used as an example only!
 *
 * Usage:
 * ```
 * $projectId = 'Your Project ID';
 * $bigQuery = require '/path/to/bigquery_client.php';
 * ```
 */
# [START build_service]
use Google\Cloud\BigQuery\BigQueryClient;

$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
# [END build_service]
return $bigQuery;
