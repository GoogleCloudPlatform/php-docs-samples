<?php

/**
 * A list of routes for our homepage. This is really only here to demonstrate
 * that PHP is being executed.
 */
$routes = ['helloworld.php'];
?>

<h1>Welcome to the Homepage!</h1>

<ul>
    <?php foreach ($routes as $route): ?>
    <li><a href="/<?= $route ?>">Go to <code><?= $route ?></code></a></li>
    <?php endforeach ?>
</ul>
