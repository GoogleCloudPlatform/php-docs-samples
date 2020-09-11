<?php

// [START run_helloworld_service]

$name = getenv('NAME', true) ?: 'World';
echo sprintf('Hello %s!', $name);

// [END run_helloworld_service]
