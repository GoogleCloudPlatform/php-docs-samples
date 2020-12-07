<?php
/*
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 4 || count($argv) > 4) {
    return printf("Usage: php %s PROJECT_ID LOCATION DATASET_ID\n", __FILE__);
}
list($_, $projectId, $location, $datasetId) = $argv;

// [START automl_language_entity_extraction_get_dataset]
// [START automl_language_sentiment_analysis_get_dataset]
// [START automl_language_text_classification_get_dataset]
// [START automl_translate_get_dataset]
// [START automl_vision_classification_get_dataset]
// [START automl_vision_object_detection_get_dataset]
use Google\Cloud\AutoMl\V1\AutoMlClient;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $datasetId = 'my_dataset_id_123';

$client = new AutoMlClient();

try {
    // get full path of dataset
    $formattedName = $client->datasetName(
        $projectId,
        $location,
        $datasetId
    );

    $dataset = $client->getDataset($formattedName);

    // display dataset information
    $splitName = explode('/', $dataset->getName());
    printf('Dataset name: %s' . PHP_EOL, $dataset->getName());
    printf('Dataset id: %s' . PHP_EOL, end($splitName));
    printf('Dataset display name: %s' . PHP_EOL, $dataset->getDisplayName());
    printf('Dataset create time' . PHP_EOL);
    printf('seconds: %d' . PHP_EOL, $dataset->getCreateTime()->getSeconds());
    printf('nanos : %d' . PHP_EOL, $dataset->getCreateTime()->getNanos());
    // [END automl_language_sentiment_analysis_get_dataset]
    // [END automl_language_text_classification_get_dataset]
    // [END automl_translate_get_dataset]
    // [END automl_vision_classification_get_dataset]
    // [END automl_vision_object_detection_get_dataset]
    printf('Text extraction dataset metadata: %s' . PHP_EOL, $dataset->getTextExtractionDatasetMetadata());
    // [END automl_language_entity_extraction_get_dataset]

    // [START automl_language_sentiment_analysis_get_dataset]
    printf('Text sentiment dataset metadata: %s' . PHP_EOL, $dataset->getTextSentimentDatasetMetadata());
    // [END automl_language_sentiment_analysis_get_dataset]

    // [START automl_language_text_classification_get_dataset]
    printf('Text classification dataset metadata: %s' . PHP_EOL, $dataset->getTextClassificationDatasetMetadata());
    // [END automl_language_text_classification_get_dataset]

    // [START automl_translate_get_dataset]
    $translationDatasetMetadata = $dataset->getTranslationDatasetMetadata();
    printf('Source language code: %s' . PHP_EOL, $translationDatasetMetadata->getSourceLanguageCode());
    printf('Target language code: %s' . PHP_EOL, $translationDatasetMetadata->getTargetLanguageCode());
    // [END automl_translate_get_dataset]

    // [START automl_vision_classification_get_dataset]
    printf('Image classification dataset metadata: %s' . PHP_EOL, $dataset->getImageClassificationDatasetMetadata());
    // [END automl_vision_classification_get_dataset]

    // [START automl_vision_object_detection_get_dataset]
    printf('Image object detection dataset metadata: %s' . PHP_EOL, $dataset->getImageObjectDetectionDatasetMetadata());
    // [START automl_language_entity_extraction_get_dataset]
    // [START automl_language_sentiment_analysis_get_dataset]
    // [START automl_language_text_classification_get_dataset]
    // [START automl_translate_get_dataset]
    // [START automl_vision_classification_get_dataset]
} finally {
    $client->close();
}
// [END automl_language_entity_extraction_get_dataset]
// [END automl_language_sentiment_analysis_get_dataset]
// [END automl_language_text_classification_get_dataset]
// [END automl_translate_get_dataset]
// [END automl_vision_classification_get_dataset]
// [END automl_vision_object_detection_get_dataset]
