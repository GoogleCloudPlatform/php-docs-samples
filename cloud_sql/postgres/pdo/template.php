<!--
Copyright 2018 Google LLC

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
-->
<html lang="en">
<head>
    <title>Tabs VS Spaces</title>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</head>
<body>
<nav class="red lighten-1">
    <div class="nav-wrapper">
        <a href="#" class="brand-logo center">Tabs VS Spaces</a>
    </div>
</nav>
<div class="section">
    <div class="center">
        <h4>
            <?php if ($tab_count == $space_count): ?>
            TABS and SPACES are evenly matched!
            <?php elseif ($tab_count > $space_count): ?>
            TABS are winning by <?=$tab_count - $space_count?>
            <?= $tab_count - $space_count > 1 ? "votes" : "vote" ?>!
            <?php elseif ($space_count > $tab_count): ?>
            SPACES are winning by <?=$space_count - $tab_count?>
            <?= $space_count - $tab_count > 1 ? "votes" : "vote" ?>!
            <?php endif ?>
        </h4>
    </div>
    <div class="row center">
        <div class="col s6 m5 offset-m1">
            <div class="card-panel <?= $tab_count > $space_count ?: 'green lighten-3' ?>">
                <i class="material-icons large">keyboard_tab</i>
                <h3><?=$tab_count?> votes</h3>
                <button id="voteTabs" class="btn green">Vote for TABS</button>
            </div>
        </div>
        <div class="col s6 m5">
            <div class="card-panel <?= $tab_count < $space_count ?: 'blue lighten-3' ?>">
                <i class="material-icons large">space_bar</i>
                <h3><?=$space_count?> votes</h3>
                <button id="voteSpaces" class="btn blue">Vote for SPACES</button>
            </div>
        </div>
    </div>
    <h4 class="header center">Recent Votes</h4>
    <ul class="container collection center">
        <?php foreach ($list as $vote): ?>
            <li class="collection-item avatar">
                <?php if ($vote['candidate'] == "TABS"): ?>
                    <i class="material-icons circle green">keyboard_tab</i>
                <?php elseif ($vote['candidate'] == "SPACES"): ?>
                    <i class="material-icons circle blue">space_bar</i>
                <?php endif ?>
            <span class="title">
                    A vote for <b><?= $vote['candidate'] ?></b>
                </span>
            <p>was cast at <?= $vote['time_cast'] ?></p>
        </li>
        <?php endforeach ?>
    </ul>
</div>
<script>
    function vote(team) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState == 4) {
                if (!window.alert(this.responseText)) {
                    window.location.reload();
                }
            }
        };
        xhr.open("POST", "/", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("team=" + team);
    }
    document.getElementById("voteTabs").addEventListener("click", function () {
        vote("TABS");
    });
    document.getElementById("voteSpaces").addEventListener("click", function () {
        vote("SPACES");
    });
</script>
</body>
</html>
