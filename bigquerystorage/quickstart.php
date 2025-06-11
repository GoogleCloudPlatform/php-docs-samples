<?php
/**
 * Copyright 2023 Google LLC
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

# [START bigquerystorage_quickstart]
// Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\BigQuery\Storage\V1\Client\BigQueryReadClient;
use Google\Cloud\BigQuery\Storage\V1\CreateReadSessionRequest;
use Google\Cloud\BigQuery\Storage\V1\DataFormat;
use Google\Cloud\BigQuery\Storage\V1\ReadRowsRequest;
use Google\Cloud\BigQuery\Storage\V1\ReadSession;
use Google\Cloud\BigQuery\Storage\V1\ReadSession\TableModifiers;
use Google\Cloud\BigQuery\Storage\V1\ReadSession\TableReadOptions;
use Google\Protobuf\Timestamp;

// Instantiates the client and sets the project
$client = new BigQueryReadClient();
$project = $client->projectName('YOUR_PROJECT_ID');
$snapshotMillis = 'YOUR_SNAPSHOT_MILLIS';

// This example reads baby name data from the below public dataset.
$table = $client->tableName(
    'bigquery-public-data',
    'usa_names',
    'usa_1910_current'
);

//  This API can also deliver data serialized in Apache Arrow format.
//  This example leverages Apache Avro.
$readSession = new ReadSession();
$readSession->setTable($table)->setDataFormat(DataFormat::AVRO);

// We limit the output columns to a subset of those allowed in the table,
// and set a simple filter to only report names from the state of
// Washington (WA).
$readOptions = new TableReadOptions();
$readOptions->setSelectedFields(['name', 'number', 'state']);
$readOptions->setRowRestriction('state = "WA"');
$readSession->setReadOptions($readOptions);

// With snapshot millis if present
if (!empty($snapshotMillis)) {
    $timestamp = new Timestamp();
    $timestamp->setSeconds($snapshotMillis / 1000);
    $timestamp->setNanos((int) ($snapshotMillis % 1000) * 1000000);
    $tableModifier = new TableModifiers();
    $tableModifier->setSnapshotTime($timestamp);
    $readSession->setTableModifiers($tableModifier);
}

try {
    $createReadSessionRequest = (new CreateReadSessionRequest())
        ->setParent($project)
        ->setReadSession($readSession)
        ->setMaxStreamCount(1);
    $session = $client->createReadSession($createReadSessionRequest);
    $readRowsRequest = (new ReadRowsRequest())
        ->setReadStream($session->getStreams()[0]->getName());
    $stream = $client->readRows($readRowsRequest);
    // Do any local processing by iterating over the responses. The
    // google-cloud-bigquery-storage client reconnects to the API after any
    // transient network errors or timeouts.
    $schema = '';
    $names = [];
    $states = [];
    foreach ($stream->readAll() as $response) {
        $data = $response->getAvroRows()->getSerializedBinaryRows();
        if ($response->hasAvroSchema()) {
            $schema = $response->getAvroSchema()->getSchema();
        }
        $avroSchema = AvroSchema::parse($schema);
        $readIO = new AvroStringIO($data);
        $datumReader = new AvroIODatumReader($avroSchema);

        while (!$readIO->is_eof()) {
            $record = $datumReader->read(new AvroIOBinaryDecoder($readIO));
            $names[$record['name']] = '';
            $states[$record['state']] = '';
        }
    }
    $states = array_keys($states);
    printf(
        'Got %d unique names in states: %s' . PHP_EOL,
        count($names),
        implode(', ', $states)
    );
} finally {
    $client->close();
}
# [END bigquerystorage_quickstart]
