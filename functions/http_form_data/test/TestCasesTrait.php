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
declare(strict_types=1);

namespace Google\Cloud\Samples\Functions\HelloworldHttp\Test;

trait TestCasesTrait
{
    public static function errorCases(): array
    {
        return [
            [
                'label' => 'Empty',
                'method' => 'post',
                'multipart' => [],
                'expected' => 'no files sent for upload',
                'status_code' => '400',
            ],
            // Fails on DeployTest with 400 error. curl returns:
            // curl: (92) HTTP/2 stream 0 was not closed cleanly: PROTOCOL_ERROR (err 1)
            // [
            //     'label' => 'Wrong Method',
            //     'method' => 'get',
            //     'multipart' => [
            //         [
            //             'name' => 'no_get_upload',
            //             'contents' => 'No upload on GET request',
            //             'filename' => 'no-get.txt',
            //         ]
            //     ],
            //     'expected' => 'Method Not Allowed: expected POST, found GET',
            //     'code' => '405',
            // ],
            [
                'label' => 'No Files',
                'method' => 'post',
                'multipart' => [
                    [
                        'name' => 'field_name',
                        'contents' => 'Bob Ross',
                    ]
                ],
                'expected' => 'no files sent for upload',
                'status_code' => '400',
            ],
        ];
    }

    public static function cases(): array
    {
        return [
            [
                'label' => 'File Upload (with filename)',
                'method' => 'post',
                'multipart' => [
                    [
                        'name' => 'file2',
                        'contents' => fopen(__DIR__ . '/fixtures/upload.txt', 'r'),
                        'filename' => 'rename.txt',
                    ]
                ],
                'expected' => 'Saved rename.txt',
                'status_code' => '201',
            ],
            [
                'label' => 'File Upload (inline)',
                'method' => 'post',
                'multipart' => [
                    [
                        'name' => 'inline_file',
                        'contents' => 'Painting is chill',
                        'filename' => 'inline_file.txt',
                    ]
                ],
                'expected' => 'Saved inline_file.txt',
                'status_code' => '201',
            ],
            [
                'label' => 'File Upload (multiple)',
                'method' => 'post',
                'multipart' => [
                    [
                        'name' => 'fire',
                        'contents' => 'Painting is chill',
                        'filename' => 'painting.txt',
                    ],
                    [
                        'name' => 'ice',
                        'contents' => 'Ice is chill',
                        'filename' => 'ice.txt',
                    ]
                ],
                'expected' => 'Saved painting.txt, ice.txt',
                'status_code' => '201',
            ],
            [
                // name property is the same for both files, so only the last file is handled.
                'label' => 'File Upload (multiple, overriding)',
                'method' => 'post',
                'multipart' => [
                    [
                        'name' => 'file1',
                        'contents' => 'Painting is chill',
                        'filename' => 'painting.txt',
                    ],
                    [
                        'name' => 'file1',
                        'contents' => 'Ice is chill',
                        'filename' => 'ice.txt',
                    ]
                ],
                'expected' => 'Saved ice.txt',
                'status_code' => '201',
            ],
        ];
    }
}
