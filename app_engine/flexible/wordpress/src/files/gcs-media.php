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

/**
 * @package GCS media
 * @version 0.1
 */
/*
Plugin Name: GCS media
Plugin URI: https://tmatsuo-wordpress.appspot.com/
Description: Use Google Cloud Storage for media upload.
Author: Takashi Matsuo
Version: 0.1
Author URI: https://tmatsuo-wordpress.appspot.com/
*/

// This plugin changes the destination of your media files to `gs://` URL
// pointing to your Google Cloud Storage bucket and change the public URL of
// the media files to the URLs starting with https://storage.googleapis.com/.
// On environments like App Engine, your instances are ephermeral and the
// local file system is not shared among multiple instances, so we need the
// central place for storing and serving your media files.

namespace GCS\Media;

defined('ABSPATH') or die('No direct access!');

add_filter('upload_dir', 'GCS\Media\filter_upload_dir');
function filter_upload_dir($values)
{
    $basedir = 'gs://' . GOOGLE_CLOUD_STORAGE_BUCKET
        . '/' . get_current_blog_id();
    $baseurl = 'https://storage.googleapis.com/'
        . GOOGLE_CLOUD_STORAGE_BUCKET
        . '/' . get_current_blog_id();
    $values = array(
        'path' => $basedir . $values['subdir'],
        'subdir' => $values['subdir'],
        'error' => false,
    );
    $values['url'] = rtrim($baseurl . $values['subdir'], '/');
    $values['basedir'] = $basedir;
    $values['baseurl'] = $baseurl;
    return $values;
}
