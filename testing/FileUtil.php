<?php
/**
 * Created by IntelliJ IDEA.
 * User: rennie
 * Date: 6/14/16
 * Time: 3:40 PM
 */

namespace Google\Cloud\TestUtils;

class FileUtil
{
    public static function randomName($length)
    {
        $array = array();
        for ($i = 0; $i < $length; ++$i) {
            array_push($array, chr(random_int(ord('a'), ord('z') + 1)));
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
