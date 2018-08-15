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
// [START vision_face_detection]
namespace Google\Cloud\Samples\Vision;

// [START vision_face_detection_tutorial_imports]
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

// [END vision_face_detection_tutorial_imports]

function detect_face($path, $outFile = null)
{
    // [START vision_face_detection_tutorial_client]
    $imageAnnotator = new ImageAnnotatorClient();
    // [END vision_face_detection_tutorial_client]

    // [START vision_face_detection_tutorial_send_request]
    # annotate the image
    // $path = 'path/to/your/image.jpg'
    $image = file_get_contents($path);
    $response = $imageAnnotator->faceDetection($image);
    $faces = $response->getFaceAnnotations();
    // [END vision_face_detection_tutorial_send_request]

    # names of likelihood from google.cloud.vision.enums
    $likelihoodName = ['UNKNOWN', 'VERY_UNLIKELY', 'UNLIKELY',
    'POSSIBLE','LIKELY', 'VERY_LIKELY'];

    printf("%d faces found:" . PHP_EOL, count($faces));
    foreach ($faces as $face) {
        $anger = $face->getAngerLikelihood();
        printf("Anger: %s" . PHP_EOL, $likelihoodName[$anger]);

        $joy = $face->getJoyLikelihood();
        printf("Joy: %s" . PHP_EOL, $likelihoodName[$joy]);

        $surprise = $face->getSurpriseLikelihood();
        printf("Surprise: %s" . PHP_EOL, $likelihoodName[$surprise]);

        # get bounds
        $vertices = $face->getBoundingPoly()->getVertices();
        $bounds = [];
        foreach ($vertices as $vertex) {
            $bounds[] = sprintf('(%d,%d)', $vertex->getX(), $vertex->getY());
        }
        print('Bounds: ' . join(', ',$bounds) . PHP_EOL);
        print(PHP_EOL);
    }
    // [END vision_face_detection]

    # [START vision_face_detection_tutorial_process_response]
    # draw box around faces
    if ($faces && $outFile) {
        $imageCreateFunc = [
            'png' => 'imagecreatefrompng',
            'gd' => 'imagecreatefromgd',
            'gif' => 'imagecreatefromgif',
            'jpg' => 'imagecreatefromjpeg',
            'jpeg' => 'imagecreatefromjpeg',
        ];
        $imageWriteFunc = [
            'png' => 'imagepng',
            'gd' => 'imagegd',
            'gif' => 'imagegif',
            'jpg' => 'imagejpeg',
            'jpeg' => 'imagejpeg',
        ];

        copy($path, $outFile);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!array_key_exists($ext, $imageCreateFunc)) {
            throw new \Exception('Unsupported image extension');
        }
        $outputImage = call_user_func($imageCreateFunc[$ext], $outFile);

        foreach ($faces as $face) {
            $vertices = $face->getBoundingPoly()->getVertices();
            if ($vertices) {
                $x1 = $vertices[0]->getX();
                $y1 = $vertices[0]->getY();
                $x2 = $vertices[2]->getX();
                $y2 = $vertices[2]->getY();
                imagerectangle($outputImage, $x1, $y1, $x2, $y2, 0x00ff00);
            }
        }
        # [END vision_face_detection_tutorial_process_response]
        # [START vision_face_detection_tutorial_run_application]
        call_user_func($imageWriteFunc[$ext], $outputImage, $outFile);
        printf('Output image written to %s' . PHP_EOL, $outFile);
        # [END vision_face_detection_tutorial_run_application]
    }

    $imageAnnotator->close();
    // [START vision_face_detection]
}
// [END vision_face_detection]
