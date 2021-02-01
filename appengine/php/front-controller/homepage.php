<?php

/**
 * A list of links to display on the homepage. This is really only here to demonstrate
 * that PHP is being executed.
 */
$links = ['contact.php'];
?>

<h1>Welcome to the Homepage!</h1>

<ul>
    <?php foreach ($links as $link): ?>
        <li>Go to <a href="/<?= $link ?>"><code><?= $link ?></code></a></li>
    <?php endforeach ?>
    <li>This page will (correctly) 404: <code><a href="/homepage.php">homepage.php</code></a></li>
</ul>
