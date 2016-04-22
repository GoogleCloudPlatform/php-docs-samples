<?php

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        'concat_with_spaces',
        'unused_use',
        'trailing_spaces',
        'indentation'
    ])
;
