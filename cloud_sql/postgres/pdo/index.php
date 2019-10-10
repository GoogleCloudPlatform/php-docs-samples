<?php
# Copyright 2018 Google LLC
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

require_once 'src/DB.php';
require_once 'src/Votes.php';

use Google\Cloud\Samples\CloudSQL\Postgres\DB;
use Google\Cloud\Samples\CloudSQL\Postgres\Votes;

$votes = new Votes(DB::createPdoConnection());

if ($_SERVER['REQUEST_URI'] == '/' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $list = $votes->list();

    $vote_count = $votes->count_candidates();
    $tab_count = $vote_count['tabs'];
    $space_count = $vote_count['spaces'];

    include_once("./template.php");
} elseif ($_SERVER['REQUEST_URI'] == '/' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = 'Invalid vote. Choose Between TABS and SPACES';

    if (!empty($_POST['team']) && in_array($_POST['team'], ['SPACES', 'TABS'])) {
        $message = $votes->save($_POST['team']);
    }

    echo $message;
}
