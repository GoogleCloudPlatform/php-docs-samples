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

use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for product search commands.
 */
class productSearchTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait {
        ExecuteCommandTrait::runCommand as traitRunCommand;
    }

    // to run the tests
    private static $commandFile = __DIR__ . '/../product_search.php';

    // for testing the creation and deletion of product sets, products, and images
    private static $productSetId = 'fake_product_set_id_for_testing_2';
    private static $productId = 'fake_product_id_for_testing_3';
    private static $referenceImageId = 'fake_reference_image_id_for_testing';

    // for testing importing products and product sets from a GCS file
    private static $importProductSetUri = 'gs://cloud-samples-data/vision/product_search/product_sets.csv';
    private static $importProductSetId = 'fake_product_set_id_for_testing';
    private static $importProductId1 = 'fake_product_id_for_testing_1';
    private static $importProductId2 = 'fake_product_id_for_testing_2';

    // for testing indexed products
    private static $indexedProductSetUri = 'gs://cloud-samples-data/vision/product_search/indexed_product_sets.csv';
    private static $indexedProductSetId = 'indexed_product_set_id_for_testing';
    private static $indexedProductId1 = 'indexed_product_id_for_testing_1';
    private static $indexedProductId2 = 'indexed_product_id_for_testing_2';

    // shared
    private static $shoesOneUri = 'gs://cloud-samples-data/vision/product_search/shoes_1.jpg';
    private static $shoesTwoUri = 'gs://cloud-samples-data/vision/product_search/shoes_2.jpg';

    public function testIndexedProductSetExists()
    {
        $output = $this->runCommand('product-set-list');
        if (false === strpos($output, self::$indexedProductSetId)) {
            print('Indexed product set does not exist, starting import...' . PHP_EOL);
            $this->importIndexedProductSet();
            $this->fail(sprintf('Index does not exist for product set %s' . PHP_EOL, self::$indexedProductSetId));
        } else {
            $output = $this->runCommand('product-set-get', [
                'product-set-id' => self::$indexedProductSetId,
            ]);
            if (false !== strpos($output, 'Product set index time: 0 seconds 0 nanos')) {
                $this->fail(sprintf('Index is still being created for product set %s', self::$indexedProductSetId));
            }
        }
    }

    /** @depends testIndexedProductSetExists */
    public function testGetSimilarProductsLocal()
    {
        $output = $this->runCommand('product-search-similar', [
            'product-set-id' => self::$indexedProductSetId,
            'product-category' => 'apparel',
            'file-path' => __DIR__ . '/data/shoes_1.jpg',
            'filter' => ''
        ]);
        $this->assertContains(self::$indexedProductId1, $output);
        $this->assertContains(self::$indexedProductId2, $output);
    }

    /** @depends testIndexedProductSetExists */
    public function testGetSimilarProductsGcs()
    {
        $output = $this->runCommand('product-search-similar-gcs', [
            'product-set-id' => self::$indexedProductSetId,
            'product-category' => 'apparel',
            'gcs-uri' => self::$shoesOneUri,
            'filter' => ''
        ]);
        $this->assertContains(self::$indexedProductId1, $output);
        $this->assertContains(self::$indexedProductId2, $output);
    }

    /** @depends testIndexedProductSetExists */
    public function testGetSimilarProductsLocalFilter()
    {
        $output = $this->runCommand('product-search-similar', [
            'product-set-id' => self::$indexedProductSetId,
            'product-category' => 'apparel',
            'file-path' => __DIR__ . '/data/shoes_1.jpg',
            'filter' => 'style=womens'
        ]);
        $this->assertContains(self::$indexedProductId1, $output);
        $this->assertNotContains(self::$indexedProductId2, $output);
    }

    /** @depends testIndexedProductSetExists */
    public function testGetSimilarProductsGcsFilter()
    {
        $output = $this->runCommand('product-search-similar-gcs', [
            'product-set-id' => self::$indexedProductSetId,
            'product-category' => 'apparel',
            'gcs-uri' => self::$shoesOneUri,
            'filter' => 'style=womens'
        ]);
        $this->assertContains(self::$indexedProductId1, $output);
        $this->assertNotContains(self::$importProductId2, $output);
    }

    public function testImportProductSets()
    {
        # run command
        $output = $this->runCommand('product-set-import', [
            'gcs-uri' => self::$importProductSetUri
        ]);
        $this->assertNotContains('Error: ', $output);

        # verify
        $output = $this->runCommand('product-set-list', []);
        $this->assertContains(self::$importProductSetId, $output);

        $output = $this->runCommand('product-list', []);
        $this->assertContains(self::$importProductId1, $output);
        $this->assertContains(self::$importProductId2, $output);

        $output = $this->runCommand('product-set-list-products', [
            'product-set-id' => self::$importProductSetId
        ]);
        $this->assertContains(self::$importProductId1, $output);
        $this->assertContains(self::$importProductId2, $output);

        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$importProductId1
        ]);
        $this->assertContains(self::$shoesOneUri, $output);

        $output = $this->runCommand('product-image-list', [
            'product-id' => self::$importProductId2
        ]);
        $this->assertContains(self::$shoesTwoUri, $output);
    }

    public function testCreateProductSet()
    {
        $this->runCommand('product-set-create', [
            'product-set-id' => self::$productSetId,
            'product-set-display-name' => 'fake_product_set_display_name_for_testing',
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
            'product-display-name' => 'fake_product_display_name_for_testing',
            'product-category' => 'apparel'
        ]);
        $output = $this->runCommand('product-list', []);
        $this->assertContains(self::$productId, $output);
    }

    /** @depends testCreateProduct */
    public function testUpdateProduct()
    {
        $key = 'fake_key_for_testing';
        $value = 'fake_value_for_testing';
        # check
        $output = $this->runCommand('product-get', [
            'product-id' => self::$productId
        ]);
        $this->assertNotContains($key, $output);
        $this->assertNotContains($value, $output);

        # test
        $output = $this->runCommand('product-update', [
            'product-id' => self::$productId,
            'key' => $key,
            'value' => $value
        ]);
        $this->assertContains($key, $output);
        $this->assertContains($value, $output);
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

    /** @depends testCreateProduct */
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

    /** @depends testCreateProduct */
    public function testPurgeOrphan()
    {
        # test
        $this->runCommand('product-purge-orphan', [
            'force' => true
        ]);
        $output = $this->runCommand('product-list', []);
        # check
        $this->assertNotContains(self::$productId, $output);
    }

    /** @depends testAddProductToProductSet */
    public function testPurgeProductsInProductSet()
    {
        $this->runCommand('product-purge-products-in-product-set', [
            'product-set-id' => self::$productSetId,
            'force' => true
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

    private function importIndexedProductSet()
    {
        printf('Importing %s' . PHP_EOL, self::$indexedProductSetUri);

        $output = $this->runCommand('product-set-import', [
            'gcs-uri' => self::$indexedProductSetUri,
        ]);
        print($output);
    }

    public static function tearDownAfterClass()
    {
        print('Cleaning up products and product sets' . PHP_EOL);
        self::runCommand('product-delete', [
            'product-id' => self::$productId
        ]);
        self::runCommand('product-delete', [
            'product-id' => self::$importProductId1
        ]);
        self::runCommand('product-delete', [
            'product-id' => self::$importProductId2
        ]);
        self::runCommand('product-set-delete', [
            'product-set-id' => self::$productSetId
        ]);
        self::runCommand('product-set-delete', [
            'product-set-id' => self::$importProductSetId
        ]);
    }

    private static function runCommand($commandName, array $args = [])
    {
        $args['project-id'] = self::$projectId;
        $args['location'] = 'us-west1';

        return self::traitRunCommand($commandName, $args);
    }
}
