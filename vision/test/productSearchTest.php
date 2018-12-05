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
    private static $key;
    private static $value;
    private static $localFile;
    private static $filter;

    public function setUp()
    {
        self::$bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::$productDisplayName = 'fake_product_display_name_for_testing';
        self::$productCategory = 'apparel';
        self::$productSetId = 'fake_product_set_id_for_testing';
        self::$productId = 'fake_product_id_for_testing';
        self::$productIdOne = 'fake_product_id_for_testing_1';
        self::$productIdTwo = 'fake_product_id_for_testing_2';
        self::$referenceImageId = 'fake_reference_image_id_for_testing';
        self::$shoesOneUri = 'gs://cloud-samples-data/vision/product_search/shoes_1.jpg';
        self::$shoesTwoUri = 'gs://cloud-samples-data/vision/product_search/shoes_2.jpg';
        self::$productSetDisplayName = 'fake_product_set_display_name_for_testing';
        self::$productSetsUri = 'gs://' . self::$bucketName . '/vision/product_sets.csv';
        self::$indexedProductsSetsUri = 'gs://' . self::$bucketName . '/vision/indexed_products_sets.csv';
        self::$key = 'fake_key_for_testing';
        self::$value = 'fake_value_for_testing';
        self::$localFile = __DIR__ . '/data/shoes_1.jpg';
        self::$filter = 'style=womens';
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

    public function testAddProductToProductSet()
    {
        # set up
        $this->runCommand('product-set-create', [
            'product-set-id' => self::$productSetId,
            'product-set-display-name' => self::$productSetDisplayName
        ]);
        $this->runCommand('product-create', [
            'product-id' => self::$productId,
            'product-display-name' => self::$productDisplayName,
            'product-category' => self::$productCategory
        ]);

        # check
        $output = $this->runCommand('product-set-list-products', [
            'product-set-id' => self::$productSetId
        ]);
        $this->assertNotContains(self::$productId, $output);

        # test
        $output = $this->runCommand('product-set-add-product', [
            'product-id' => self::$productId,
            'product-set-id' => self::$productSetId
        ]);
        $output = $this->runCommand('product-set-list-products', [
            'product-set-id' => self::$productSetId
        ]);
        $this->assertContains(self::$productId, $output);

        # tear down
        $this->runCommand('product-delete', [
            'product-id' => self::$productId
        ]);
        $this->runCommand('product-set-delete', [
            'product-set-id' => self::$productSetId
        ]);
    }

    public function testRemoveProductFromProductSet()
    {
        # set up
        $this->runCommand('product-set-create', [
            'product-set-id' => self::$productSetId,
            'product-set-display-name' => self::$productSetDisplayName
        ]);
        $this->runCommand('product-create', [
            'product-id' => self::$productId,
            'product-display-name' => self::$productDisplayName,
            'product-category' => self::$productCategory
        ]);

        # check
        $output = $this->runCommand('product-set-add-product', [
            'product-id' => self::$productId,
            'product-set-id' => self::$productSetId
        ]);
        $output = $this->runCommand('product-set-list-products', [
            'product-set-id' => self::$productSetId
        ]);
        $this->assertContains(self::$productId, $output);

        # test
        $output = $this->runCommand('product-set-remove-product', [
            'product-id' => self::$productId,
            'product-set-id' => self::$productSetId
        ]);
        $output = $this->runCommand('product-set-list-products', [
            'product-set-id' => self::$productSetId
        ]);
        $this->assertNotContains(self::$productId, $output);

        # tear down
        $this->runCommand('product-delete', [
            'product-id' => self::$productId
        ]);
        $this->runCommand('product-set-delete', [
            'product-set-id' => self::$productSetId
        ]);
    }

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

        # tear down
        $this->runCommand('product-delete', [
            'product-id' => self::$productId
        ]);
    }

    public function testDeleteProduct()
    {
        # set up
        $this->runCommand('product-create', [
            'product-id' => self::$productId,
            'product-display-name' => self::$productDisplayName,
            'product-category' => self::$productCategory
        ]);
        $output = $this->runCommand('product-list', []);
        $this->assertContains(self::$productId, $output);

        # test
        $this->runCommand('product-delete', [
            'product-id' => self::$productId
        ]);
        $output = $this->runCommand('product-list', []);
        $this->assertNotContains(self::$productId, $output);
    }

    public function testUpdateProduct()
    {
        # set up
        $this->runCommand('product-create', [
            'product-id' => self::$productId,
            'product-display-name' => self::$productDisplayName,
            'product-category' => self::$productCategory
        ]);

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

        # tear down
        $this->runCommand('product-delete', [
            'product-id' => self::$productId
        ]);
    }

    public function testGetSimilarProductsLocal()
    {
        # set up
        $this->runCommand('product-set-import', [
            'gcs-uri' => self::$productSetsUri
        ]);

        # test
        $output = $this->runCommand('product-search-similar', [
            'product-set-id' => self::$productSetId,
            'product-category' => self::$productCategory,
            'file-path' => self::$localFile,
            'filter' => ''
        ]);
        $this->assertContains(self::$productIdOne, $output);
        $this->assertContains(self::$productIdTwo, $output);

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

    public function testGetSimilarProductsGcs()
    {
        # set up
        $this->runCommand('product-set-import', [
            'gcs-uri' => self::$productSetsUri
        ]);

        $output = $this->runCommand('product-search-similar-gcs', [
            'product-set-id' => self::$productSetId,
            'product-category' => self::$productCategory,
            'gcs-uri' => self::$shoesOneUri,
            'filter' => ''
        ]);
        $this->assertContains(self::$productIdOne, $output);
        $this->assertContains(self::$productIdTwo, $output);

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

    public function testGetSimilarProductsLocalFilter()
    {
        # set up
        $this->runCommand('product-set-import', [
            'gcs-uri' => self::$productSetsUri
        ]);

        $output = $this->runCommand('product-search-similar', [
            'product-set-id' => self::$productSetId,
            'product-category' => self::$productCategory,
            'file-path' => self::$localFile,
            'filter' => self::$filter
        ]);
        $this->assertContains(self::$productIdOne, $output);
        $this->assertNotContains(self::$productIdTwo, $output);

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

    public function testGetSimilarProductsGcsFilter()
    {
        # set up
        $this->runCommand('product-set-import', [
            'gcs-uri' => self::$productSetsUri
        ]);

        $output = $this->runCommand('product-search-similar-gcs', [
            'product-set-id' => self::$productSetId,
            'product-category' => self::$productCategory,
            'gcs-uri' => self::$shoesOneUri,
            'filter' => self::$filter
        ]);
        $this->assertContains(self::$productIdOne, $output);
        $this->assertNotContains(self::$productIdTwo, $output);

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

    public function testCreateProductSet()
    {
        # pre-check
        $output = $this->runCommand('product-set-list', []);
        $this->assertNotContains(self::$productSetId, $output);

        # test
        $this->runCommand('product-set-create', [
            'product-set-id' => self::$productSetId,
            'product-set-display-name' => self::$productSetDisplayName
        ]);
        $output = $this->runCommand('product-set-list', []);
        $this->assertContains(self::$productSetId, $output);

        # tear down
        $this->runCommand('product-set-delete', [
            'product-set-id' => self::$productSetId
        ]);
    }

    public function testDeleteProductSet()
    {
        # set up
        $this->runCommand('product-set-create', [
            'product-set-id' => self::$productSetId,
            'product-set-display-name' => self::$productSetDisplayName
        ]);

        # pre-check
        $output = $this->runCommand('product-set-list', []);
        $this->assertContains(self::$productSetId, $output);

        # test
        $this->runCommand('product-set-delete', [
            'product-set-id' => self::$productSetId
        ]);
        $output = $this->runCommand('product-set-list', []);
        $this->assertNotContains(self::$productSetId, $output);
    }

    public function testCreateImage()
    {
        # set up
        $this->runCommand('product-create', [
            'product-id' => self::$productId,
            'product-display-name' => self::$productDisplayName,
            'product-category' => self::$productCategory
        ]);

        # pre check
        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$productId
        ]);
        $this->assertNotContains(self::$referenceImageId, $output);

        # test
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

    public function testDeleteImage()
    {
        # set up
        $this->runCommand('product-create', [
            'product-id' => self::$productId,
            'product-display-name' => self::$productDisplayName,
            'product-category' => self::$productCategory
        ]);
        $this->runCommand('product-image-create', [
            'product-id' => self::$productId,
            'reference-image-id' => self::$referenceImageId,
            'gcs-uri' => self::$shoesOneUri
        ]);

        # pre check
        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$productId
        ]);
        $this->assertContains(self::$referenceImageId, $output);

        # test
        $this->runCommand('product-image-delete', [
            'product-id' => self::$productId,
            'reference-image-id' => self::$referenceImageId
        ]);
        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$productId
        ]);
        $this->assertNotContains(self::$referenceImageId, $output);

        # tear down
        $this->runCommand('product-delete', [
            'product-id' => self::$productId
        ]);
    }
}
