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

namespace google\appengine\api\cloud_storage;

class CloudStorageTools
{
    public static $contentType;
    public static $metadata;
    public static $uploadUrl;
    public static $served;
    public static $publicUrl;
    public static $imageUrl;
    public static $imageOptions;

    public static function getDefaultGoogleStorageBucketName()
    {
        return 'default_bucket_name';
    }

    public static function createUploadUrl()
    {
        return self::$uploadUrl;
    }

    public static function getContentType($fp)
    {
        return self::$contentType;
    }

    public static function getMetaData($fp)
    {
        return self::$metadata;
    }

    public static function serve($file)
    {
        self::$served = file_get_contents($file);
    }

    public static function getPublicUrl($file)
    {
        return self::$publicUrl = $file;
    }

    public static function getImageServingUrl($file, $options)
    {
        self::$imageOptions = $options;
        return self::$imageUrl = $file;
    }
}
