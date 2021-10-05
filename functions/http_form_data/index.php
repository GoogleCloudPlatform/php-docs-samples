<?php
/**
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

// [START functions_http_form_data]

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;

function uploadFile(ServerRequestInterface $request): ResponseInterface
{
    if ($request->getMethod() != 'POST') {
        return new Response(405, [], 'Method Not Allowed: expected POST, found ' . $request->getMethod());
    }

    $contentType = $request->getHeader('Content-Type')[0];
    if (strpos($contentType, 'multipart/form-data') !== 0) {
        return new Response(400, [], 'Bad Request: content of type "multipart/form-data" not provided, found ' . $contentType);
    }

    $fileList = [];
    /** @var $file Psr\Http\Message\UploadedFileInterface */
    foreach ($request->getUploadedFiles() as $name => $file) {
        // Use caution when trusting the client-provided filename:
        // https://owasp.org/www-community/vulnerabilities/Unrestricted_File_Upload
        $fileList[] = $file->getClientFilename();

        infoLog('Processing ' . $file->getClientFilename());
        $filename = tempnam(sys_get_temp_dir(), $name . '.') . '-' . $file->getClientFilename();

        // Use $file->getStream() to process the file contents in ways other than a direct "file save".
        infoLog('Saving to ' . $filename);
        $file->moveTo($filename);
    }

    if (empty($fileList)) {
        $msg = 'Bad Request: no files sent for upload';
        errorLog($msg);
        return new Response(400, [], $msg);
    }

    return new Response(201, [], 'Saved ' . join(', ', $fileList));
}

function errorLog($msg): void
{
    $stream = fopen('php://stderr', 'wb');
    $entry = json_encode(['msg' => $msg, 'severity' => 'error'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    fwrite($stream, $entry . PHP_EOL);
}

function infoLog($msg): void
{
    $stream = fopen('php://stderr', 'wb');
    $entry = json_encode(['message' => $msg, 'severity' => 'info'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    fwrite($stream, $entry . PHP_EOL);
}

// [END functions_http_form_data]
