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

require_once __DIR__ . '/vendor/autoload.php';

if (count($argv) !== 2) {
    exit("Usage: php add_composer_scripts.php [composer file]\n");
}

$composerFile = $argv[1];
if (!file_exists($composerFile)) {
    exit("Could not find composer.json, file does not exist\n");
}

if (!$contents = file_get_contents($composerFile)) {
    exit("Could not open composer.json\n");
}

if (!$json = json_decode($contents, true)) {
    exit("Could not parse composer.json\n");
}

if (!isset($json['scripts'])) {
    $json['scripts'] = [];
}

$scriptsContents = file_get_contents(__DIR__ . '/composer_scripts.json');
$scripts = json_decode($scriptsContents, true)['scripts'];

$newScripts = $json['scripts'];
foreach ($scripts as $name => $script) {
    if (!isset($newScripts[$name])) {
        $newScripts[$name] = [];
    }
    $newScripts[$name] = array_unique(array_merge($newScripts[$name], $script));
}
if ($json['scripts'] == $newScripts) {
    exit("composer.json is already configured\n");
}

$json['scripts'] = $newScripts;
file_put_contents($composerFile, json_encode($json, JSON_PRETTY_PRINT));

echo "composer.json is now configured\n";
