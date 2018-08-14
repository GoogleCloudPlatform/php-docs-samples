<?php

echo "Post-Update Command\n";

file_put_contents(__DIR__ . '/test.txt', 'This text was added by your friendly ' . __FILE__);
