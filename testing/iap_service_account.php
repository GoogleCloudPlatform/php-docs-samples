<?php
/**
 * Dumps the contents of the environment variable IAP_CREDENTIALS_BASE64 to
 * a file.
 */

$iap_cred = getenv('IAP_CREDENTIALS_BASE64');
$iap_fpath = getenv('IAP_SERVICE_ACCOUNT');
if ($iap_cred !== false && $iap_fpath !== false) {
    file_put_contents($iap_fpath, base64_decode($iap_cred));
}
