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

namespace Google\Cloud\Samples\Language;

use Google\Cloud\NaturalLanguage\Annotation;
use Symfony\Component\Yaml\Yaml;

/**
 * Convert an Annotation to a string.
 *
 * @param Annotation $annotation
 * @return string
 */
function annotation_to_string(Annotation $annotation)
{
    return Yaml::dump($annotation->info(), 20, 2);
}
