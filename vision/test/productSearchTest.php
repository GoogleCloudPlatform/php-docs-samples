<?php
/**
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


namespace Google\Cloud\Samples\Vision;

/**
 * Unit Tests for product search commands.
 */
class productSearchTest extends \PHPUnit_Framework_TestCase
{
    use ProductSearchTestTrait;

    private static $bucketName;
    private static $productDisplayName;
    private static $productCategory;
    private static $productSetId;
    private static $productId;
    private static $productIdOne;
    private static $productIdTwo;
    private static $referenceImageId;
    private static $shoesOneUri;
    private static $shoesTwoUri;
    private static $productSetDisplayName;
    private static $productSetsUri;
    private static $indexedProductsSetsUri;

    public function setUp()
    {
        self::$bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::$productDisplayName = 'fake_product_display_name_for_testing';
        self::$productCategory = 'homegoods';
        self::$productSetId = 'fake_product_set_id_for_testing';
        self::$productId = 'fake_product_id_for_testing';
        self::$productIdOne = 'fake_product_id_for_testing_1';
        self::$productIdTwo = 'fake_product_id_for_testing_2';
        self::$referenceImageId = 'fake_reference_image_id_for_testing';
        self::$shoesOneUri = 'gs://' . self::$bucketName . '/vision/shoes_1.jpg';
        self::$shoesTwoUri = 'gs://' . self::$bucketName . '/vision/shoes_2.jpg';
        self::$productSetDisplayName = 'fake_product_set_display_name_for_testing';
        self::$productSetsUri = 'gs://' . self::$bucketName . '/vision/product_sets.csv';
        self::$indexedProductsSetsUri = 'gs://' . self::$bucketName . '/vision/indexed_products_sets.csv';
    }

    public function testImportProductSets()
    {
        # pre-check
        $output = $this->runCommand('product-set-list', []);
        $this->assertNotContains(self::$productSetId, $output);

        $output = $this->runCommand('product-list', []);
        $this->assertNotContains(self::$productIdOne, $output);
        $this->assertNotContains(self::$productIdTwo, $output);

        $output = $this->runCommand('product-set-list-products', [
            'product-set-id' => self::$productSetId
        ]);
        $this->assertNotContains(self::$productIdOne, $output);
        $this->assertNotContains(self::$productIdTwo, $output);

        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$productIdOne
        ]);
        $this->assertNotContains(self::$shoesOneUri, $output);

        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$productIdTwo
        ]);
        $this->assertNotContains(self::$shoesTwoUri, $output);


        # run command
        $this->runCommand('product-set-import', [
            'gcs-uri' => self::$productSetsUri
        ]);


        # post check
        $output = $this->runCommand('product-set-list', []);
        $this->assertContains(self::$productSetId, $output);

        $output = $this->runCommand('product-list', []);
        $this->assertContains(self::$productIdOne, $output);
        $this->assertContains(self::$productIdTwo, $output);

        $output = $this->runCommand('product-set-list-products', [
            'product-set-id' => self::$productSetId
        ]);
        $this->assertContains(self::$productIdOne, $output);
        $this->assertContains(self::$productIdTwo, $output);

        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$productIdOne
        ]);
        $this->assertContains(self::$shoesOneUri, $output);

        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$productIdTwo
        ]);
        $this->assertContains(self::$shoesTwoUri, $output);

        # clean up
        $this->runCommand('product-delete', [
            'product-id' => self::$productIdOne
        ]);

        $this->runCommand('product-delete', [
            'product-id' => self::$productIdTwo
        ]);

        $this->runCommand('product-set-delete', [
            'product-set-id' => self::$productSetId
        ]);
    }
}
