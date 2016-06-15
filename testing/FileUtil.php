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

namespace Google\Cloud\TestUtils;

class FileUtil
{
    public static function randomName($length)
    {
        $array = array();
        for ($i = 0; $i < $length; ++$i) {
            array_push($array, chr(random_int(ord('a'), ord('z'))));
        }
        return join('', $array);
    }

    public static function cloneDirectoryIntoTmp($projectDir = '.')
    {
        $tmpDir = sys_get_temp_dir() . '/test-' . self::randomName(8);
        self::copyDir($projectDir, $tmpDir);
        return $tmpDir;
    }

    public static function copyDir($src, $dst)
    {
        @mkdir($dst);
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($src . '/' . $file)) {
                self::copyDir($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
        closedir($dir);
    }
}
