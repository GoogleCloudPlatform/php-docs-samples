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

// [START dlp_delete_inspect_templates]
use Google\Cloud\Dlp\V2\DlpServiceClient;

/**
 * List DLP inspection configuration templates.
 * @param string $callingProject The GCP Project ID to run the API call under
 */
function delete_inspect_templates ($templateId) {
  // Instantiate a client.
  $dlp = new DlpServiceClient();

  // Run template deletion request
  $dlp->deleteInspectTemplate($templateId);

  // Print results
  print_r('Successfully deleted template ' . $templateId . PHP_EOL);
}
// [END dlp_delete_inspect_templates]