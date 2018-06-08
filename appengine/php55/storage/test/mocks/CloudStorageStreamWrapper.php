<?php
/**
 * Copyright 2016 Google Inc.
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

namespace google\appengine\ext\cloud_storage_streams;

class CloudStorageStreamWrapper
{
    public static $path = null;
    public static $data = [];

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        self::$path = $path;
        return true;
    }

    public function stream_stat()
    {
        if (isset(self::$data[self::$path])) {
            return [];
        }
    }

    public function stream_read($count)
    {
        if (!array_key_exists(self::$path, self::$data)) {
            return false;
        }

        $data = self::$data[self::$path];
        self::$data[self::$path] = null;
        return $data;
    }

    public function stream_eof()
    {
        if (self::$data[self::$path]) {
            return false;
        }
        return true;
    }

    public function url_stat($path, $flags)
    {
        if (!array_key_exists($path, self::$data)) {
            return false;
        }

        $size = strlen(self::$data[self::$path]);
        return [
            'dev' => 0,
            'ino' => 0,
            'mode' => 'r',
            'nlink' => 0,
            'uid' => getmyuid(),
            'gid' => getmygid(),
            'rdev' => 0,
            'size' => $size,
            'atime' => time(),
            'mtime' => time(),
            'ctime' => time(),
            'blksize' => -1,
            'blocks' => -1
        ];
    }

    public function stream_write($data)
    {
        self::$data[self::$path] = $data;
        return strlen($data);
    }
}
