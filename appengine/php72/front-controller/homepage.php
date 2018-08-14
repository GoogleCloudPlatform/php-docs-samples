<?php

/**
 * A list of routes for our homepage. This is really only here to demonstrate
 * that PHP is being executed.
 */
$routes = ['contact.php'];
?>

<h1>Welcome to the Homepage!</h1>

<ul>
    <?php foreach ($routes as $route): ?>
        <li>Go to <a href="/<?= $route ?>"><code><?= $route ?></code></a></li>
    <?php endforeach ?>
    <li>This page will (correctly) 404: <code><a href="/homepage.php">homepage.php</code></a></li>
</ul>
