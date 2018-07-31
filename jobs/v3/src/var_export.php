<?php

namespace Google\Cloud\Samples\Jobs;

use Google_Model;

/**
 * Trick the samples into using this var_export instead of the built in PHP one.
 */
function var_export($object, $return = false)
{
    if (!$object instanceof Google_Model) {
        return var_export($object, $return);
    }
    $export = json_encode($object->toSimpleObject(), JSON_PRETTY_PRINT);
    if ($return) {
        return $export;
    }
    print $export;
}
