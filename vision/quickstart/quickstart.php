<?php

# [START vision_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Vision\VisionClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$vision = new VisionClient([
    'projectId' => $projectId
]);

# The name of the image file to annotate
$fileName = __DIR__ . '/resources/wakeupcat.jpg';

# Prepare the image to be annotated
$image = $vision->image(fopen($fileName, 'r'), [
    'LABEL_DETECTION'
]);

# Performs label detection on the image file
$labels = $vision->annotate($image)->labels();

echo "Labels:\n";
foreach ($labels as $label) {
    echo $label->description() . "\n";
}
# [END vision_quickstart]
return $labels;
