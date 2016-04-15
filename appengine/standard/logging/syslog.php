<?php

$user_is_authorized = !empty($_GET['authorized']);

# [START syslog]
if ($user_is_authorized) {
    syslog(LOG_INFO, 'Authorized access');
    echo 'true';
} else {
    syslog(LOG_WARNING, 'Unauthorized access');
    echo 'false';
}
# [END syslog]
