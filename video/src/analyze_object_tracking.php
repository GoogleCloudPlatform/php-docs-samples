<?php

/**
 * Copyright 2019 Google Inc.
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

namespace Google\Cloud\Samples\VideoIntelligence;

// [START video_object_tracking_gcs]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;

/**
 * @param string $uri The cloud storage object to analyze (gs://your-bucket-name/your-object-name)
 * @param int $pollingIntervalSeconds
 */
function analyze_object_tracking(string $uri, int $pollingIntervalSeconds = 0)
{
    # Instantiate a client.
    $video = new VideoIntelligenceServiceClient();

    # Execute a request.
    $features = [Feature::OBJECT_TRACKING];
    $operation = $video->annotateVideo([
        'inputUri' => $uri,
        'features' => $features,
    ]);

    # Wait for the request to complete.
    $operation->pollUntilComplete([
        'pollingIntervalSeconds' => $pollingIntervalSeconds
    ]);

    # Print the results.
    if ($operation->operationSucceeded()) {
        $results = $operation->getResult()->getAnnotationResults()[0];
        # Process video/segment level label annotations
        $objectEntity = $results->getObjectAnnotations()[0];

        printf('Video object entity: %s' . PHP_EOL, $objectEntity->getEntity()->getEntityId());
        printf('Video object description: %s' . PHP_EOL, $objectEntity->getEntity()->getDescription());

        $start = $objectEntity->getSegment()->getStartTimeOffset();
        $end = $objectEntity->getSegment()->getEndTimeOffset();
        printf('  Segment: %ss to %ss' . PHP_EOL,
            $start->getSeconds() + $start->getNanos() / 1000000000.0,
            $end->getSeconds() + $end->getNanos() / 1000000000.0);
        printf('  Confidence: %f' . PHP_EOL, $objectEntity->getConfidence());

        foreach ($objectEntity->getFrames() as $objectEntityFrame) {
            $offset = $objectEntityFrame->getTimeOffset();
            $boundingBox = $objectEntityFrame->getNormalizedBoundingBox();
            printf('  Time offset: %ss' . PHP_EOL,
                $offset->getSeconds() + $offset->getNanos() / 1000000000.0);
            printf('  Bounding box position:' . PHP_EOL);
            printf('   Left: %s', $boundingBox->getLeft());
            printf('   Top: %s', $boundingBox->getTop());
            printf('   Right: %s', $boundingBox->getRight());
            printf('   Bottom: %s', $boundingBox->getBottom());
        }
        print(PHP_EOL);
    } else {
        print_r($operation->getError());
    }
}
// [END video_object_tracking_gcs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
