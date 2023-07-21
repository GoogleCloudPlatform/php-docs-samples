<?php
/**
 * Copyright 2022 Google LLC.
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

/**
 * Google Analytics Data API sample application demonstrating the usage of
 * property quota metadata.
 * See https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/runReport#body.request_body.FIELDS.return_property_quota
 * for more information.
 * Usage:
 *   composer update
 *   php run_report_with_property_quota.php YOUR-GA4-PROPERTY-ID
 */

namespace Google\Cloud\Samples\Analytics\Data;

// [START analyticsdata_run_report_with_property_quota]
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\RunReportResponse;

/**
 * Runs a report and prints property quota information.
 * @param string $propertyId Your GA-4 Property ID
 */
function run_report_with_property_quota(string $propertyId)
{
    // Create an instance of the Google Analytics Data API client library.
    $client = new BetaAnalyticsDataClient();

    // Make an API call.
    $request = (new RunReportRequest())
        ->setProperty('properties/' . $propertyId)
        ->setReturnPropertyQuota(true)
        ->setDimensions([new Dimension(['name' => 'country'])])
        ->setMetrics([new Metric(['name' => 'activeUsers'])])
        ->setDateRanges([
            new DateRange([
                'start_date' => '7daysAgo',
                'end_date' => 'today',
            ]),
        ]);
    $response = $client->runReport($request);

    printRunReportResponseWithPropertyQuota($response);
}

/**
 * Print results of a runReport call.
 * @param RunReportResponse $response
 */
function printRunReportResponseWithPropertyQuota(RunReportResponse $response)
{
    // [START analyticsdata_run_report_with_property_quota_print_response]
    if ($response->hasPropertyQuota()) {
        $propertyQuota = $response->getPropertyQuota();
        $tokensPerDay = $propertyQuota->getTokensPerDay();
        $tokensPerHour = $propertyQuota->getTokensPerHour();
        $concurrentRequests = $propertyQuota->getConcurrentRequests();
        $serverErrors = $propertyQuota->getServerErrorsPerProjectPerHour();
        $thresholdedRequests = $propertyQuota->getPotentiallyThresholdedRequestsPerHour();

        printf(
            'Tokens per day quota consumed: %s, remaining: %s' . PHP_EOL,
            $tokensPerDay->getConsumed(),
            $tokensPerDay->getRemaining(),
        );
        printf(
            'Tokens per hour quota consumed: %s, remaining: %s' . PHP_EOL,
            $tokensPerHour->getConsumed(),
            $tokensPerHour->getRemaining(),
        );
        printf(
            'Concurrent requests quota consumed: %s, remaining: %s' . PHP_EOL,
            $concurrentRequests->getConsumed(),
            $concurrentRequests->getRemaining(),
        );
        printf(
            'Server errors per project per hour quota consumed: %s, remaining: %s' . PHP_EOL,
            $serverErrors->getConsumed(),
            $serverErrors->getRemaining(),
        );
        printf(
            'Potentially thresholded requests per hour quota consumed: %s, remaining: %s' . PHP_EOL,
            $thresholdedRequests->getConsumed(),
            $thresholdedRequests->getRemaining(),
        );
    }
    // [END analyticsdata_run_report_with_property_quota_print_response]
}
// [END analyticsdata_run_report_with_property_quota]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
