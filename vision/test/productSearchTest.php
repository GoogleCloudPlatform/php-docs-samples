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

    private static $productDisplayName = 'fake_product_display_name_for_testing';
    private static $productCategory = 'apparel';
    private static $productSetId = 'fake_product_set_id_for_testing';
    private static $productId = 'fake_product_id_for_testing';
    private static $productIdOne = 'fake_product_id_for_testing_1';
    private static $productIdTwo = 'fake_product_id_for_testing_2';
    private static $referenceImageId = 'fake_reference_image_id_for_testing';
    private static $shoesOneUri = 'gs://cloud-samples-data/vision/product_search/shoes_1.jpg';
    private static $shoesTwoUri = 'gs://cloud-samples-data/vision/product_search/shoes_2.jpg';
    private static $productSetDisplayName = 'fake_product_set_display_name_for_testing';
    private static $productSetsUri = 'gs://cloud-samples-data/vision/product_search/product_sets.csv';
    private static $key = 'fake_key_for_testing';
    private static $value = 'fake_value_for_testing';
    private static $localFile = __DIR__ . '/data/shoes_1.jpg';
    private static $filter = 'style=womens';

    public function testImportProductSets()
    {
        # run command
        $this->runCommand('product-set-import', [
            'gcs-uri' => self::$productSetsUri
        ]);

        # verify
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

        # delete everything we created
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

    public function testCreateProductSet()
    {
        $this->runCommand('product-set-create', [
            'product-set-id' => self::$productSetId,
            'product-set-display-name' => self::$productSetDisplayName
        ]);
        $output = $this->runCommand('product-set-list', []);
        $this->assertContains(self::$productSetId, $output);
    }

    /** @depends testCreateProductSet */
    public function testCreateProduct()
    {
        # check
        $output = $this->runCommand('product-list', []);
        $this->assertNotContains(self::$productId, $output);

        # test
        $this->runCommand('product-create', [
            'product-id' => self::$productId,
            'product-display-name' => self::$productDisplayName,
            'product-category' => self::$productCategory
        ]);
        $output = $this->runCommand('product-list', []);
        $this->assertContains(self::$productId, $output);
    }

    /** @depends testCreateProduct */
    public function testUpdateProduct()
    {
        # check
        $output = $this->runCommand('product-get', [
            'product-id' => self::$productId
        ]);
        $this->assertNotContains(self::$key, $output);
        $this->assertNotContains(self::$value, $output);

        # test
        $output = $this->runCommand('product-update', [
            'product-id' => self::$productId,
            'key' => self::$key,
            'value' => self::$value
        ]);
        $this->assertContains(self::$key, $output);
        $this->assertContains(self::$value, $output);
    }

    /** @depends testUpdateProduct */
    public function testAddProductToProductSet()
    {
        $output = $this->runCommand('product-set-add-product', [
            'product-id' => self::$productId,
            'product-set-id' => self::$productSetId
        ]);
        $output = $this->runCommand('product-set-list-products', [
            'product-set-id' => self::$productSetId
        ]);
        $this->assertContains(self::$productId, $output);
    }

    /** @depends testAddProductToProductSet */
    public function testRemoveProductFromProductSet()
    {
        $output = $this->runCommand('product-set-remove-product', [
            'product-id' => self::$productId,
            'product-set-id' => self::$productSetId
        ]);
        $output = $this->runCommand('product-set-list-products', [
            'product-set-id' => self::$productSetId
        ]);
        $this->assertNotContains(self::$productId, $output);
    }

    /** @depends testImportProductSets */
    public function testGetSimilarProductsLocal()
    {
        $output = $this->runCommand('product-search-similar', [
            'product-set-id' => self::$productSetId,
            'product-category' => self::$productCategory,
            'file-path' => self::$localFile,
            'filter' => ''
        ]);
        $this->assertContains(self::$productIdOne, $output);
        $this->assertContains(self::$productIdTwo, $output);
    }

    /** @depends testImportProductSets */
    public function testGetSimilarProductsGcs()
    {
        $output = $this->runCommand('product-search-similar-gcs', [
            'product-set-id' => self::$productSetId,
            'product-category' => self::$productCategory,
            'gcs-uri' => self::$shoesOneUri,
            'filter' => ''
        ]);
        $this->assertContains(self::$productIdOne, $output);
        $this->assertContains(self::$productIdTwo, $output);
    }

    /** @depends testImportProductSets */
    public function testGetSimilarProductsLocalFilter()
    {
        $output = $this->runCommand('product-search-similar', [
            'product-set-id' => self::$productSetId,
            'product-category' => self::$productCategory,
            'file-path' => self::$localFile,
            'filter' => self::$filter
        ]);
        $this->assertContains(self::$productIdOne, $output);
        $this->assertNotContains(self::$productIdTwo, $output);
    }

    /** @depends testImportProductSets */
    public function testGetSimilarProductsGcsFilter()
    {
        $output = $this->runCommand('product-search-similar-gcs', [
            'product-set-id' => self::$productSetId,
            'product-category' => self::$productCategory,
            'gcs-uri' => self::$shoesOneUri,
            'filter' => self::$filter
        ]);
        $this->assertContains(self::$productIdOne, $output);
        $this->assertNotContains(self::$productIdTwo, $output);
    }

    /** @depends testImportProductSets */
    public function testCreateImage()
    {
        $this->runCommand('product-image-create', [
            'product-id' => self::$productId,
            'reference-image-id' => self::$referenceImageId,
            'gcs-uri' => self::$shoesOneUri
        ]);
        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$productId
        ]);
        $this->assertContains(self::$referenceImageId, $output);

        # tear down
        $this->runCommand('product-delete', [
            'product-id' => self::$productId
        ]);
    }

    /** @depends testCreateImage */
    public function testDeleteImage()
    {
        $this->runCommand('product-image-delete', [
            'product-id' => self::$productId,
            'reference-image-id' => self::$referenceImageId
        ]);
        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$productId
        ]);
        $this->assertNotContains(self::$referenceImageId, $output);
    }

    /** @depends testCreateProduct */
    public function testDeleteProduct()
    {
        $this->runCommand('product-delete', [
            'product-id' => self::$productId
        ]);
        $output = $this->runCommand('product-list', []);
        $this->assertNotContains(self::$productId, $output);
    }

    /** @depends testCreateProductSet */
    public function testDeleteProductSet()
    {
        $this->runCommand('product-set-delete', [
            'product-set-id' => self::$productSetId
        ]);
        $output = $this->runCommand('product-set-list', []);
        $this->assertNotContains(self::$productSetId, $output);
    }

    public static function tearDownAfterClass()
    {
        print('Cleaning up products and product sets...' . PHP_EOL);
        self::runCommand('product-delete', [
            'product-id' => self::$productId
        ]);
        self::runCommand('product-delete', [
            'product-id' => self::$productIdOne
        ]);
        self::runCommand('product-delete', [
            'product-id' => self::$productIdTwo
        ]);
        self::runCommand('product-set-delete', [
            'product-set-id' => self::$productSetId
        ]);
    }
}
