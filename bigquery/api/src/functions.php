<?php

namespace Google\Cloud\Samples\BigQuery;

/**
 * these functions are used to send the command's output though the
 * Console component, but still appear as standard PHP functions in
 * the code samples
 */
function printf()
{
    global $output;
    $args = func_get_args();
    if ($output) {
        $output->write(call_user_func_array('sprintf', $args));
    } else {
        call_user_func_array('printf', $args);
    }
}
