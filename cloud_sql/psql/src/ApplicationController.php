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

namespace Google\Cloud\Samples\CloudSQL\Postgres;

use Google\Cloud\Samples\CloudSQL\Postgres\DB;
use Google\Cloud\Samples\CloudSQL\Postgres\Votes;

class ApplicationController
{
    private $votes;

    public function __construct()
    {
        $this->votes = new Votes(new DB());
    }

    public function index()
    {
        $list = $this->votes->list();
        $vote_count = $this->votes->count_candidates();
        $this->render($list, $vote_count['tabs'], $vote_count['spaces']);
    }

    public function vote()
    {
        if (array_key_exists('team', $_POST) && $_POST['team']) {
            echo $this->votes->save($_POST['team']);
        }
    }

    private function render($list, $tab_count, $space_count)
    {
        include_once("./template/index.php");
    }
}
