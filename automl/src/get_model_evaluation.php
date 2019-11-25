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

if (count($argv) < 5 || count($argv) > 5) {
    return printf("Usage: php %s PROJECT_ID LOCATION MODEL_ID MODEL_EVALUATION_ID\n", __FILE__);
}
list($_, $projectId, $location, $modelId, $modelEvaluationId) = $argv;

// [START automl_language_entity_extraction_get_model_evaluation]
// [START automl_language_sentiment_analysis_get_model_evaluation]
// [START automl_language_text_classification_get_model_evaluation]
// [START automl_translate_get_model_evaluation]
// [START automl_vision_classification_get_model_evaluation]
// [START automl_vision_object_detection_get_model_evaluation]
use Google\Cloud\AutoMl\V1\AutoMlClient;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $modelId = 'my_model_id_123';
// $modeEvaluationId = 'my_model_evaluation_id_123';

$client = new AutoMlClient();

try {
    // get full path of the model evaluation
    $formattedName = $client->modelEvaluationName(
        $projectId,
        $location,
        $modelId,
        $modelEvaluationId
    );

    $modelEvaluation = $client->getModelEvaluation($formattedName);

    // display model evaluation information
    $splitName = explode('/', $modelEvaluation->getName());
    printf('Model evaluation name: %s' . PHP_EOL, $modelEvaluation->getName());
    printf('Model evaluation id: %s' . PHP_EOL, end($splitName));
    printf('Model annotation spec id: %s' . PHP_EOL, $modelEvaluation->getAnnotationSpecId());
    printf('Create time' . PHP_EOL);
    printf('seconds: %d' . PHP_EOL, $modelEvaluation->getCreateTime()->getSeconds());
    printf('nanos : %d' . PHP_EOL, $modelEvaluation->getCreateTime()->getNanos());
    printf('Evaluation example count: %s' . PHP_EOL, $modelEvaluation->getEvaluatedExampleCount());
    // [END automl_language_sentiment_analysis_get_model_evaluation]
    // [END automl_language_text_classification_get_model_evaluation]
    // [END automl_translate_get_model_evaluation]
    // [END automl_vision_classification_get_model_evaluation]
    // [END automl_vision_object_detection_get_model_evaluation]
    printf('Model evaluation metrics: %s' . PHP_EOL, $modelEvaluation->getTextExtractionEvaluationMetrics());
    // [END automl_language_entity_extraction_get_model_evaluation]

    // [START automl_language_sentiment_analysis_get_model_evaluation]
    printf('Model evaluation metrics: %s' . PHP_EOL, $modelEvaluation->getTextSentimentEvaluationMetrics());
    // [END automl_language_sentiment_analysis_get_model_evaluation]

    // [START automl_language_text_classification_get_model_evaluation]
    // [START automl_vision_classification_get_model_evaluation]
    printf('Model evaluation metrics: %s' . PHP_EOL, $modelEvaluation->getTextSentimentEvaluationMetrics());
    // [END automl_language_text_classification_get_model_evaluation]
    // [START automl_vision_classification_get_model_evaluation]

    // [START automl_translate_get_model_evaluation]
    printf('Model evaluation metrics: %s' . PHP_EOL, $modelEvaluation->getTranslationEvaluationMetrics());
    // [END automl_translate_get_model_evaluation]

    // [START automl_vision_object_detection_get_model_evaluation]
    printf('Model evaluation metrics: %s' . PHP_EOL, $modelEvaluation->getImageObjectDetectionEvaluationMetrics());

    // [START automl_language_entity_extraction_get_model_evaluation]
    // [START automl_language_sentiment_analysis_get_model_evaluation]
    // [START automl_language_text_classification_get_model_evaluation]
    // [START automl_translate_get_model_evaluation]
    // [START automl_vision_classification_get_model_evaluation]
} finally {
    $client->close();
}
// [END automl_language_entity_extraction_get_model_evaluation]
// [END automl_language_sentiment_analysis_get_model_evaluation]
// [END automl_language_text_classification_get_model_evaluation]
// [END automl_translate_get_model_evaluation]
// [END automl_vision_classification_get_model_evaluation]
// [END automl_vision_object_detection_get_model_evaluation]
