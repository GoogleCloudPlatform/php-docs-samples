<?php
/*
 * Copyright 2020 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Google\Cloud\Samples\Kms;

// [START kms_encrypt_asymmetric]
function encrypt_asymmetric(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key',
    string $versionId = '123',
    string $plaintext = '...'
): void {
    // PHP has limited support for asymmetric encryption operations.
    // Specifically, openssl_public_encrypt() does not allow customizing
    // algorithms or padding. Thus, it is not currently possible to use PHP
    // core for asymmetric operations on RSA keys.
    //
    // Third party libraries like phpseclib may provide the required
    // functionality. Google does not endorse this external library.
}
// [END kms_encrypt_asymmetric]
