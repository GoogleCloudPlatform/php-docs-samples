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
namespace Google\Cloud\Samples\Dlp;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

$application = new Application('Cloud DLP');

$application->add(new Command('inspect-string'))
    ->addArgument('string', InputArgument::REQUIRED, 'The text to inspect')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Inspect a string.')
    ->addArgument(
        'max-findings',
        InputArgument::OPTIONAL,
        'The maximum number of findings to report per request (0 = server maximum)',
        0
    )
    ->setCode(function ($input, $output) {
        inspect_string(
            (string) $input->getArgument('calling-project'),
            $input->getArgument('string')
        );
    });

$application->add(new Command('inspect-file'))
    ->addArgument('path', InputArgument::REQUIRED, 'The file path to inspect')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Inspect a file.')
    ->addArgument(
        'max-findings',
        InputArgument::OPTIONAL,
        'The maximum number of findings to report per request (0 = server maximum)',
        0
    )
    ->setCode(function ($input, $output) {
        inspect_file(
            (string) $input->getArgument('calling-project'),
            $input->getArgument('path')
        );
    });

$application->add(new Command('inspect-datastore'))
    ->addArgument('kind', InputArgument::REQUIRED, 'The Datastore kind to inspect')
    ->addArgument('topic-id', InputArgument::REQUIRED, 'The name of the Pub/Sub topic to notify once the job completes')
    ->addArgument('subscription-id', InputArgument::REQUIRED, 'The name of the Pub/Sub subscription to use when listening for job')
    ->addArgument('namespace', InputArgument::OPTIONAL, 'The ID namespace of the Datastore document to inspect', '')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument('data-project', InputArgument::OPTIONAL, 'The GCP Project ID that the Datastore exists under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument(
        'max-findings',
        InputArgument::OPTIONAL,
        'The maximum number of findings to report per request (0 = server maximum)',
        0
    )
    ->setDescription('Inspect a Google Cloud Platform project\'s Cloud Datastore , using Pub/Sub for job status notifications.')
    ->setCode(function ($input, $output) {
        inspect_datastore(
            (string) $input->getArgument('calling-project'),
            (string) $input->getArgument('data-project'),
            $input->getArgument('topic-id'),
            $input->getArgument('subscription-id'),
            $input->getArgument('kind'),
            $input->getArgument('namespace'),
            (int) $input->getArgument('max-findings')
        );
    });

$application->add(new Command('inspect-bigquery'))
    ->addArgument('dataset', InputArgument::REQUIRED, 'The ID of the dataset to inspect')
    ->addArgument('table', InputArgument::REQUIRED, 'The ID of the table to inspect')
    ->addArgument('topic-id', InputArgument::REQUIRED, 'The name of the Pub/Sub topic to notify once the job completes')
    ->addArgument('subscription-id', InputArgument::REQUIRED, 'The name of the Pub/Sub subscription to use when listening for job')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument('data-project', InputArgument::OPTIONAL, 'The GCP Project ID that the BigQuery table exists under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument(
        'max-findings',
        InputArgument::OPTIONAL,
        'The maximum number of findings to report per request (0 = server maximum)',
        0
    )
    ->setDescription('Inspect a BigQuery table , using Pub/Sub for job status notifications.')
    ->setCode(function ($input, $output) {
        inspect_bigquery(
            (string) $input->getArgument('calling-project'),
            (string) $input->getArgument('data-project'),
            $input->getArgument('topic-id'),
            $input->getArgument('subscription-id'),
            $input->getArgument('dataset'),
            $input->getArgument('table'),
            (int) $input->getArgument('max-findings')
        );
    });

$application->add(new Command('inspect-gcs'))
    ->addArgument('bucket-id', InputArgument::REQUIRED, 'The ID of the bucket where the file resides')
    ->addArgument('file', InputArgument::REQUIRED, 'TThe path to the file within the bucket to inspect')
    ->addArgument('topic-id', InputArgument::REQUIRED, 'The name of the Pub/Sub topic to notify once the job completes')
    ->addArgument('subscription-id', InputArgument::REQUIRED, 'The name of the Pub/Sub subscription to use when listening for job')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument('data-project', InputArgument::OPTIONAL, 'The GCP Project ID that the BigQuery table exists under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument(
        'max-findings',
        InputArgument::OPTIONAL,
        'The maximum number of findings to report per request (0 = server maximum)',
        0
    )
    ->setDescription('Inspect a file stored on Google Cloud Storage , using Pub/Sub for job status notifications.')
    ->setCode(function ($input, $output) {
        inspect_gcs(
            (string) $input->getArgument('calling-project'),
            (string) $input->getArgument('bucket-id'),
            $input->getArgument('file'),
            $input->getArgument('topic-id'),
            $input->getArgument('subscription-id'),
            (int) $input->getArgument('max-findings')
        );
    });

$application->add(new Command('list-info-types'))
    ->addArgument('filter', InputArgument::OPTIONAL, 'The filter to use', '')
    ->addArgument('language-code', InputArgument::OPTIONAL, 'The text to inspect', '')
    ->setDescription('Lists all Info Types for the Data Loss Prevention (DLP) API.')
    ->setCode(function ($input, $output) {
        list_info_types(
            $input->getArgument('filter'),
            $input->getArgument('language-code')
        );
    });

$application->add(new Command('redact-image'))
    ->addArgument('image-path', InputArgument::REQUIRED, 'The local filepath of the image to inspect')
    ->addArgument('output-path', InputArgument::REQUIRED, 'The local filepath to save the resulting image to')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Redact sensitive data from an image.')
    ->setCode(function ($input, $output) {
        redact_image(
            (string) $input->getArgument('calling-project'),
            $input->getArgument('image-path'),
            $input->getArgument('output-path')
        );
    });

$application->add(new Command('deidentify-mask'))
    ->addArgument('string', InputArgument::REQUIRED, 'The text to deidentify')
    ->addArgument(
        'number-to-mask',
        InputArgument::OPTIONAL,
        'The maximum number of sensitive characters to mask in a match',
        0
    )
    ->addArgument(
        'masking-character',
        InputArgument::OPTIONAL,
        'The character to mask matching sensitive data with',
        'x'
    )
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Mask sensitive data in a string.')
    ->setCode(function ($input, $output) {
        deidentify_mask(
            (string) $input->getArgument('calling-project'),
            $input->getArgument('string'),
            (int) $input->getArgument('number-to-mask'),
            $input->getArgument('masking-character')
        );
    });

$application->add(new Command('deidentify-fpe'))
    ->addArgument('string', InputArgument::REQUIRED, 'The text to deidentify')
    ->addArgument(
        'key-name',
        InputArgument::REQUIRED,
        'The name of the Cloud KMS key used to encrypt ("wrap") the AES-256 key'
    )
    ->addArgument(
        'wrapped-key',
        InputArgument::REQUIRED,
        'The AES-256 key to use, encrypted ("wrapped") with the KMS key defined by $keyName.'
    )
    ->addArgument('surrogate-type', InputArgument::OPTIONAL, 'The name of the surrogate custom info type to use when reidentifying')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Deidentify a string using Format-Preserving Encryption (FPE).')
    ->setCode(function ($input, $output) {
        deidentify_fpe(
            (string) $input->getArgument('calling-project'),
            $input->getArgument('string'),
            $input->getArgument('key-name'),
            $input->getArgument('wrapped-key'),
            $input->getArgument('surrogate-type')
        );
    });

$application->add(new Command('reidentify-fpe'))
    ->addArgument('string', InputArgument::REQUIRED, 'The text to deidentify')
    ->addArgument(
        'key-name',
        InputArgument::REQUIRED,
        'The name of the Cloud KMS key used to encrypt ("wrap") the AES-256 key'
    )
    ->addArgument(
        'wrapped-key',
        InputArgument::REQUIRED,
        'The AES-256 key to use, encrypted ("wrapped") with the KMS key defined by $keyName.'
    )
    ->addArgument('surrogate-type', InputArgument::REQUIRED, 'The name of the surrogate custom info type to use when reidentifying')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Mask sensitive data in a string.')
    ->setCode(function ($input, $output) {
        reidentify_fpe(
            (string) $input->getArgument('calling-project'),
            $input->getArgument('string'),
            $input->getArgument('key-name'),
            $input->getArgument('wrapped-key'),
            $input->getArgument('surrogate-type')
        );
    });

$application->add(new Command('deidentify-dates'))
    ->addArgument('input-csv', InputArgument::REQUIRED, 'The path to the CSV file to deidentify')
    ->addArgument('output-csv', InputArgument::REQUIRED, 'The path to save the date-shifted CSV file to')
    ->addArgument('date-fields', InputArgument::REQUIRED, 'The list of (date) fields in the CSV file to date shift')
    ->addArgument('lower-bound-days', InputArgument::REQUIRED, 'The maximum number of days to shift a date backward')
    ->addArgument('upper-bound-days', InputArgument::REQUIRED, 'The maximum number of days to shift a date forward')

    ->addArgument(
        'key-name',
        InputArgument::OPTIONAL,
        'The name of the Cloud KMS key used to encrypt ("wrap") the AES-256 key'
    )
    ->addArgument(
        'wrapped-key',
        InputArgument::OPTIONAL,
        'The AES-256 key to use, encrypted ("wrapped") with the KMS key defined by $keyName.'
    )
    ->addArgument('context-field', InputArgument::OPTIONAL, 'The column to determine date shift amount based on. If omitted, random amounts will be used for each row.')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Deidentify dates in a CSV file by pseudorandomly shifting them.')
    ->setCode(function ($input, $output) {
        deidentify_dates(
            (string) $input->getArgument('calling-project'),
            $input->getArgument('input-csv'),
            $input->getArgument('output-csv'),
            explode(',', $input->getArgument('date-fields')),
            (int) $input->getArgument('lower-bound-days'),
            (int) $input->getArgument('upper-bound-days'),
            $input->getArgument('context-field'),
            $input->getArgument('key-name'),
            $input->getArgument('wrapped-key')
        );
    });

$application->add(new Command('create-trigger'))
    ->addArgument('bucket-name', InputArgument::REQUIRED, 'The name of the bucket to scan')
    ->addArgument('frequency', InputArgument::REQUIRED, 'How often to run the scan, in hours (minimum = 1 hour)')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument('trigger-id', InputArgument::OPTIONAL, 'The name of the trigger to be created', '')
    ->addArgument('display-name', InputArgument::OPTIONAL, 'The human-readable name to give the trigger', '')
    ->addArgument('description', InputArgument::OPTIONAL, 'A description for the trigger to be created', '')
    ->addArgument(
        'max-findings',
        InputArgument::OPTIONAL,
        'The maximum number of findings to report per request (0 = server maximum)',
        0
    )
    ->setDescription('List Data Loss Prevention API job triggers.')
    ->setCode(function ($input, $output) {
        create_trigger(
            (string) $input->getArgument('calling-project'),
            $input->getArgument('bucket-name'),
            $input->getArgument('trigger-id'),
            $input->getArgument('display-name'),
            $input->getArgument('description'),
            (int) $input->getArgument('frequency'),
            (int) $input->getArgument('max-findings')
        );
    });

$application->add(new Command('list-triggers'))
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('List Data Loss Prevention API job triggers.')
    ->setCode(function ($input, $output) {
        list_triggers(
            (string) $input->getArgument('calling-project')
        );
    });

$application->add(new Command('delete-trigger'))
    ->addArgument('trigger-id', InputArgument::REQUIRED, 'The name of the trigger to be deleted')
    ->setDescription('Delete a Data Loss Prevention API job trigger.')
    ->setCode(function ($input, $output) {
        delete_trigger($input->getArgument('trigger-id'));
    });

$application->add(new Command('list-jobs'))
    ->addArgument('filter', InputArgument::REQUIRED, 'The filter expression to use; see https://cloud.google.com/dlp/docs/reference/rest/v2/projects.dlpJobs/list')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('List Data Loss Prevention API jobs corresponding to a given filter.')
    ->setCode(function ($input, $output) {
        list_jobs(
            (string) $input->getArgument('calling-project'),
            $input->getArgument('filter')
        );
    });

$application->add(new Command('delete-job'))
    ->addArgument('job-id', InputArgument::REQUIRED, 'The name of the job to be deleted')
    ->setDescription('Delete results of a Data Loss Prevention API job.')
    ->setCode(function ($input, $output) {
        delete_job($input->getArgument('job-id'));
    });

$application->add(new Command('create-inspect-template'))
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument('template-id', InputArgument::OPTIONAL, 'The name of the template to be created.', '')
    ->addArgument('display-name', InputArgument::OPTIONAL, 'The human-readable name to give the template', '')
    ->addArgument('description', InputArgument::OPTIONAL, 'A description for the trigger to be created', '')
    ->addArgument(
        'max-findings',
        InputArgument::OPTIONAL,
        'The maximum number of findings to report per request (0 = server maximum)',
        0
    )
    ->setDescription('Create a new DLP inspection configuration template.')
    ->setCode(function ($input, $output) {
        create_inspect_template(
            (string) $input->getArgument('calling-project'),
            $input->getArgument('template-id'),
            $input->getArgument('display-name'),
            $input->getArgument('description'),
            $input->getArgument('max-findings')
        );
    });

$application->add(new Command('list-inspect-templates'))
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('List DLP inspection configuration templates.')
    ->setCode(function ($input, $output) {
        list_inspect_templates($input->getArgument('calling-project'));
    });

$application->add(new Command('delete-inspect-template'))
    ->addArgument('template-id', InputArgument::REQUIRED, 'The name of the template to delete')
    ->setDescription('Delete a DLP inspection configuration template.')
    ->setCode(function ($input, $output) {
        delete_inspect_templates($input->getArgument('template-id'));
    });

$application->add(new Command('numerical-stats'))
    ->addArgument('dataset', InputArgument::REQUIRED, 'The ID of the dataset to inspect')
    ->addArgument('table', InputArgument::REQUIRED, 'The ID of the table to inspect')
    ->addArgument('topic-id', InputArgument::REQUIRED, 'The name of the Pub/Sub topic to notify once the job completes')
    ->addArgument(
        'column-name',
        InputArgument::REQUIRED,
        'The name of the (number-type) column to compute risk metrics for, e.g. "age"'
    )
    ->addArgument('subscription-id', InputArgument::REQUIRED, 'The name of the Pub/Sub subscription to use when listening for job')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument('data-project', InputArgument::OPTIONAL, 'The GCP Project ID that the BigQuery table exists under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Computes risk metrics of a column of numbers in a Google BigQuery table.')
    ->setCode(function ($input, $output) {
        numerical_stats(
            (string) $input->getArgument('calling-project'),
            (string) $input->getArgument('data-project'),
            $input->getArgument('topic-id'),
            $input->getArgument('subscription-id'),
            $input->getArgument('dataset'),
            $input->getArgument('table'),
            $input->getArgument('column-name')
        );
    });

$application->add(new Command('categorical-stats'))
    ->addArgument('dataset', InputArgument::REQUIRED, 'The ID of the dataset to inspect')
    ->addArgument('table', InputArgument::REQUIRED, 'The ID of the table to inspect')
    ->addArgument('topic-id', InputArgument::REQUIRED, 'The name of the Pub/Sub topic to notify once the job completes')
    ->addArgument(
        'column-name',
        InputArgument::REQUIRED,
        'The name of the column to compute risk metrics for, e.g. "gender"'
    )
    ->addArgument('subscription-id', InputArgument::REQUIRED, 'The name of the Pub/Sub subscription to use when listening for job')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument('data-project', InputArgument::OPTIONAL, 'The GCP Project ID that the BigQuery table exists under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Computes risk metrics of a column of data in a Google BigQuery table.')
    ->setCode(function ($input, $output) {
        categorical_stats(
            (string) $input->getArgument('calling-project'),
            (string) $input->getArgument('data-project'),
            $input->getArgument('topic-id'),
            $input->getArgument('subscription-id'),
            $input->getArgument('dataset'),
            $input->getArgument('table'),
            $input->getArgument('column-name')
        );
    });

$application->add(new Command('k-anonymity'))
    ->addArgument('dataset', InputArgument::REQUIRED, 'The ID of the dataset to inspect')
    ->addArgument('table', InputArgument::REQUIRED, 'The ID of the table to inspect')
    ->addArgument('topic-id', InputArgument::REQUIRED, 'The name of the Pub/Sub topic to notify once the job completes')
    ->addArgument(
        'quasi-ids',
        InputArgument::REQUIRED,
        'A set of columns that form a composite key ("quasi-identifiers")'
    )
    ->addArgument('subscription-id', InputArgument::REQUIRED, 'The name of the Pub/Sub subscription to use when listening for job')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument('data-project', InputArgument::OPTIONAL, 'The GCP Project ID that the BigQuery table exists under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Computes the k-anonymity of a column set in a Google BigQuery table.')
    ->setCode(function ($input, $output) {
        k_anonymity(
            (string) $input->getArgument('calling-project'),
            (string) $input->getArgument('data-project'),
            $input->getArgument('topic-id'),
            $input->getArgument('subscription-id'),
            $input->getArgument('dataset'),
            $input->getArgument('table'),
            explode(",", $input->getArgument('quasi-ids'))
        );
    });

$application->add(new Command('l-diversity'))
    ->addArgument('dataset', InputArgument::REQUIRED, 'The ID of the dataset to inspect')
    ->addArgument('table', InputArgument::REQUIRED, 'The ID of the table to inspect')
    ->addArgument('topic-id', InputArgument::REQUIRED, 'The name of the Pub/Sub topic to notify once the job completes')
    ->addArgument(
        'quasi-ids',
        InputArgument::REQUIRED,
        'A set of columns that form a composite key ("quasi-identifiers")'
    )
    ->addArgument('sensitive-attribute', InputArgument::REQUIRED, 'The column to measure l-diversity relative to, e.g. "firstName"')
    ->addArgument('subscription-id', InputArgument::REQUIRED, 'The name of the Pub/Sub subscription to use when listening for job')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument('data-project', InputArgument::OPTIONAL, 'The GCP Project ID that the BigQuery table exists under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Computes the l-diversity of a column set in a Google BigQuery table.')
    ->setCode(function ($input, $output) {
        l_diversity(
            (string) $input->getArgument('calling-project'),
            (string) $input->getArgument('data-project'),
            $input->getArgument('topic-id'),
            $input->getArgument('subscription-id'),
            $input->getArgument('dataset'),
            $input->getArgument('table'),
            $input->getArgument('sensitive-attribute'),
            explode(",", $input->getArgument('quasi-ids'))
        );
    });

$application->add(new Command('k-map'))
    ->addArgument('dataset', InputArgument::REQUIRED, 'The ID of the dataset to inspect')
    ->addArgument('table', InputArgument::REQUIRED, 'The ID of the table to inspect')
    ->addArgument('topic-id', InputArgument::REQUIRED, 'The name of the Pub/Sub topic to notify once the job completes')
    ->addArgument(
        'quasi-ids',
        InputArgument::REQUIRED,
        'A set of columns that form a composite key ("quasi-identifiers")'
    )
    ->addArgument(
        'info-types',
        InputArgument::REQUIRED,
        'The infoTypes corresponding to the chosen quasi-identifiers'
    )
    ->addArgument('subscription-id', InputArgument::REQUIRED, 'The name of the Pub/Sub subscription to use when listening for job')
    ->addArgument('region-code', InputArgument::OPTIONAL, 'The ISO 3166-1 region code that the data is representative of', 'US')
    ->addArgument('calling-project', InputArgument::OPTIONAL, 'The GCP Project ID to run the API call under', getenv('GOOGLE_PROJECT_ID'))
    ->addArgument('data-project', InputArgument::OPTIONAL, 'The GCP Project ID that the BigQuery table exists under', getenv('GOOGLE_PROJECT_ID'))
    ->setDescription('Computes the k-map risk estimation of a column set in a Google BigQuery table.')
    ->setCode(function ($input, $output) {
        k_map(
            (string) $input->getArgument('calling-project'),
            (string) $input->getArgument('data-project'),
            $input->getArgument('topic-id'),
            $input->getArgument('subscription-id'),
            $input->getArgument('dataset'),
            $input->getArgument('table'),
            $input->getArgument('region-code'),
            explode(",", $input->getArgument('quasi-ids')),
            explode(",", $input->getArgument('info-types'))
        );
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
