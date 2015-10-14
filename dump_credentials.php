<?php
/**
 * Dumps the contents of the environment variable GOOGLE_CREDENTIALS_BASE64 to
 * a file.
 *
 * To setup Travis to run on your fork, read TRAVIS.md.
 */
file_put_contents(
    getenv('GOOGLE_APPLICATION_CREDENTIALS'),
    base64_decode(getenv('GOOGLE_CREDENTIALS_BASE64'))
);
