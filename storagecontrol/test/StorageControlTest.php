namespace Google\Cloud\Samples\StorageControl;

use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Tests for storage control library samples.
 */
class StorageControlTest extends TestCase
{
    use TestTrait;
    use EventuallyConsistentTestTrait;

    private static $sourceBucket;
    private static $folderId;
    private static $managedFolderId;
    private static $folderName;
    private static $managedFolderName;
    private static $storage;
    private static $storageControlClient;
    private static $location;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$storage = new StorageClient();
        self::$storageControlClient = new StorageControlClient();
        self::$location = 'us-west1';
        $uniqueBucketId = time() . rand();
        self::$folderId = time() . rand();
        self::$managedFolderId = time() . rand();
        self::$sourceBucket = self::$storage->createBucket(
            sprintf('php-gcscontrol-sample-%s', $uniqueBucketId),
            [
                'location' => self::$location,
                'hierarchicalNamespace' => ['enabled' => true],
                'iamConfiguration' => ['uniformBucketLevelAccess' => ['enabled' => true]]
            ]
        );
        self::$folderName = self::$storageControlClient->folderName(
            '_',
            self::$sourceBucket->name(),
            self::$folderId
        );
        self::$managedFolderName = self::$storageControlClient->managedFolderName(
            '_',
            self::$sourceBucket->name(),
            self::$managedFolderId
        );
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$sourceBucket->objects(['versions' => true]) as $object) {
            $object->delete();
        }
        self::$sourceBucket->delete();
    }

    public function testCreateFolder()
    {
        $output = $this->runFunctionSnippet('create_folder', [
            self::$sourceBucket->name(), self::$folderId
        ]);

        $this->assertStringContainsString(
            sprintf('Created folder: %s', self::$folderName),
            $output
        );
    }

    public function testManagedCreateFolder()
    {
        $output = $this->runFunctionSnippet('managed_folder_create', [
            self::$sourceBucket->name(), self::$managedFolderId
        ]);

        $this->assertStringContainsString(
            sprintf('Performed createManagedFolder request for %s', self::$managedFolderName),
            $output
        );
    }

    /**
     * @depends testCreateFolder
     */
    public function testManagedGetFolder()
    {
        $output = $this->runFunctionSnippet('managed_folder_get', [
            self::$sourceBucket->name(), self::$managedFolderId
        ]);

        $this->assertStringContainsString(
            sprintf('Got Managed Folder %s', self::$managedFolderName),
            $output
        );
    }

    /**
     * @depends testManagedGetFolder
     */
    public function testManagedListFolders()
    {
        $output = $this->runFunctionSnippet('managed_folders_list', [
            self::$sourceBucket->name()
        ]);

        $this->assertStringContainsString(
            sprintf('%s bucket has managed folder %s', self::$sourceBucket->name(), self::$managedFolderName),
            $output
        );
    }

    /**
     * @depends testManagedListFolders
     */
    public function testManagedDeleteFolder()
    {
        $output = $this->runFunctionSnippet('managed_folder_delete', [
            self::$sourceBucket->name(), self::$managedFolderId
        ]);

        $this->assertStringContainsString(
            sprintf('Deleted Managed Folder %s', self::$managedFolderId),
            $output
        );
    }

    /**
     * @depends testCreateFolder
     */
    public function testGetFolder()
    {
        $output = $this->runFunctionSnippet('get_folder', [
            self::$sourceBucket->name(), self::$folderId
        ]);

        $this->assertStringContainsString(
            self::$folderName,
            $output
        );
    }

    /**
     * @depends testGetFolder
     */
    public function testListFolders()
    {
        $output = $this->runFunctionSnippet('list_folders', [
            self::$sourceBucket->name()
        ]);

        $this->assertStringContainsString(
            self::$folderName,
            $output
        );
    }

    /**
     * @depends testListFolders
     */
    public function testRenameFolder()
    {
        $newFolderId = time() . rand();
        $output = $this->runFunctionSnippet('rename_folder', [
            self::$sourceBucket->name(), self::$folderId, $newFolderId
        ]);

        $this->assertStringContainsString(
            sprintf('Renamed folder %s to %s', self::$folderId, $newFolderId),
            $output
        );

        self::$folderId = $newFolderId;
    }

    /**
     * @depends testRenameFolder
     */
    public function testDeleteFolder()
    {
        $output = $this->runFunctionSnippet('delete_folder', [
            self::$sourceBucket->name(), self::$folderId
        ]);

        $this->assertStringContainsString(
            sprintf('Deleted folder: %s', self::$folderId),
            $output
        );
    }

    /**
     * @depends testDeleteFolder
     */
    public function testDeleteFolderRecursive()
    {
        $parentFolderId = 'test-parent-' . time() . rand();
        $childFolderId = $parentFolderId . '/test-child-' . time() . rand();

        $bucketName = self::$sourceBucket->name();
        $bucketResourceName = self::$storageControlClient->bucketName('_', $bucketName);

        // Create parent folder
        $createParentRequest = new \Google\Cloud\Storage\Control\V2\CreateFolderRequest([
            'parent' => $bucketResourceName,
            'folder_id' => $parentFolderId,
        ]);
        self::$storageControlClient->createFolder($createParentRequest);

        // Create child folder
        $createChildRequest = new \Google\Cloud\Storage\Control\V2\CreateFolderRequest([
            'parent' => $bucketResourceName,
            'folder_id' => $childFolderId,
        ]);
        self::$storageControlClient->createFolder($createChildRequest);

        // Call the delete folder recursive snippet
        $output = $this->runFunctionSnippet('delete_folder_recursive', [
            $bucketName, $parentFolderId
        ]);

        $this->assertStringContainsString(
            sprintf('Deleted folder recursively: %s', $parentFolderId),
            $output
        );

        // Verify folder is gone by trying to get the parent folder
        $formattedParentName = self::$storageControlClient->folderName('_', $bucketName, $parentFolderId);
        $getRequest = new \Google\Cloud\Storage\Control\V2\GetFolderRequest([
            'name' => $formattedParentName,
        ]);

        $this->runEventuallyConsistentTest(function () use ($getRequest) {
            try {
                self::$storageControlClient->getFolder($getRequest);
                $this->fail('Expected getFolder to throw ApiException for deleted folder');
            } catch (\Google\ApiCore\ApiException $e) {
                $this->assertEquals(404, $e->getCode());
            }
        });
    }
}
