<?php
/*
 * Copyright 2018 Google Inc.
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

use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../signUrl.php";

class signUrlTest extends TestCase
{
    public function testBase64UrlEncode()
    {
        $this->assertEquals(base64url_encode(hex2bin("9d9b51a2174d17d9b770a336e0870ae3")), "nZtRohdNF9m3cKM24IcK4w==");
    }

    public function testBase64UrlEncodeWithoutPadding()
    {
        $this->assertEquals(base64url_encode(hex2bin("9d9b51a2174d17d9b770a336e0870ae3"), false), "nZtRohdNF9m3cKM24IcK4w");
    }

    public function testBase64UrlDecode()
    {
        $this->assertEquals(hex2bin("9d9b51a2174d17d9b770a336e0870ae3"), base64url_decode("nZtRohdNF9m3cKM24IcK4w=="));
    }

    public function testBase64UrlDecodeWithoutPadding()
    {
        $this->assertEquals(hex2bin("9d9b51a2174d17d9b770a336e0870ae3"), base64url_decode("nZtRohdNF9m3cKM24IcK4w"));
    }

    public function testSignUrl()
    {
        $encoded_key="nZtRohdNF9m3cKM24IcK4w=="; // base64url encoded key

        $cases = array(
            array("http://35.186.234.33/index.html", "my-key", 1558131350,
                  "http://35.186.234.33/index.html?Expires=1558131350&KeyName=my-key&Signature=fm6JZSmKNsB5sys8VGr-JE4LiiE="),
            array("https://www.google.com/", "my-key", 1549751401,
                  "https://www.google.com/?Expires=1549751401&KeyName=my-key&Signature=M_QO7BGHi2sGqrJO-MDr0uhDFuc="),
            array("https://www.example.com/some/path?some=query&another=param", "my-key", 1549751461,
                  "https://www.example.com/some/path?some=query&another=param&Expires=1549751461&KeyName=my-key&Signature=sTqqGX5hUJmlRJ84koAIhWW_c3M="),
        );

        foreach ($cases as $c) {
            $this->assertEquals(sign_url($c[0], $c[1], $encoded_key, $c[2]), $c[3]);
        }
    }
}
