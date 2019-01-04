<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\helpers\FileHelper;
use yii\tests\TestCase;
use yii\exceptions\InvalidArgumentException;

/**
 * Unit test for [[yii\helpers\FileHelper]].
 * @see FileHelper
 * @group helpers
 */
class FileHelperTest extends TestCase
{
    /**
     * @var string test files path.
     */
    private $testFilePath = '';

    public function setUp()
    {
        parent::setUp();

        $this->testFilePath = realpath(__DIR__ . '/../../../tests/runtime/') . get_class($this);
        FileHelper::createDirectory($this->testFilePath, 0777);

        if (!file_exists($this->testFilePath)) {
            $this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
        }

        // destroy application, Helper must work without $this->app
        $this->destroyApplication();
    }

    /**
     * Check if chmod works as expected
     *
     * On remote file systems and vagrant mounts chmod returns true
     * but file permissions are not set properly.
     */
    private function isChmodReliable(): bool
    {
        $directory = $this->testFilePath . '/test_chmod';
        mkdir($directory);
        chmod($directory, 0700);
        $mode = $this->getMode($directory);
        rmdir($directory);

        return $mode === '0700';
    }

    public function tearDown()
    {
        FileHelper::removeDirectory($this->testFilePath);
    }

    /**
     * Get file permission mode.
     * @param string $file file name.
     * @return string permission mode.
     */
    private function getMode(string $file): string
    {
        return substr(sprintf('%04o', fileperms($file)), -4);
    }

    /**
     * Creates test files structure in `$this->testFilePath`.
     */
    protected function createFileStructure(array $items, string $directory = null): void
    {
        parent::createFileStructure($items, $directory ?? $this->testFilePath);
    }

    /**
     * Asserts that file has specific permission mode.
     * @param int $expectedMode expected file permission mode.
     * @param string $fileName file name.
     * @param string $message error message
     */
    private function assertFileMode(int $expectedMode, string $fileName, string $message = ''): void
    {
        $expectedMode = sprintf('%04o', $expectedMode);
        $this->assertEquals($expectedMode, $this->getMode($fileName), $message);
    }

    // Tests :

    public function testCreateDirectory(): void
    {
        $basePath = $this->testFilePath;
        $directory = $basePath . '/test_dir_level_1/test_dir_level_2';
        $this->assertTrue(FileHelper::createDirectory($directory), 'FileHelper::createDirectory should return true if directory was created!');
        $this->assertFileExists($directory, 'Unable to create directory recursively!');
        $this->assertTrue(FileHelper::createDirectory($directory), 'FileHelper::createDirectory should return true for already existing directories!');
    }


    public function testCreateDirectoryPermissions(): void
    {
        if (!$this->isChmodReliable()) {
            $this->markTestSkipped('Skipping test since chmod is not reliable in this environment.');
        }

        $basePath = $this->testFilePath;

        $dirName = $basePath . '/test_dir_perms';
        $this->assertTrue(FileHelper::createDirectory($dirName, 0700, false));
        $this->assertFileMode(0700, $dirName);
    }

    /**
     * @depends testCreateDirectory
     */
    public function testCopyDirectory(): void
    {
        $source = 'test_src_dir';
        $files = [
            'file1.txt' => 'file 1 content',
            'file2.txt' => 'file 2 content',
        ];
        $this->createFileStructure([
            $source => $files,
        ]);

        $basePath = $this->testFilePath;
        $source = $basePath . '/' . $source;
        $destination = $basePath . '/test_dst_dir';

        FileHelper::copyDirectory($source, $destination);

        $this->assertFileExists($destination, 'Destination directory does not exist!');
        foreach ($files as $name => $content) {
            $fileName = $destination . '/' . $name;
            $this->assertFileExists($fileName);
            $this->assertStringEqualsFile($fileName, $content, 'Incorrect file content!');
        }
    }

    public function testCopyDirectoryRecursive(): void
    {
        $source = 'test_src_dir_rec';
        $structure = [
            'directory1' => [
                'file1.txt' => 'file 1 content',
                'file2.txt' => 'file 2 content',
            ],
            'directory2' => [
                'file3.txt' => 'file 3 content',
                'file4.txt' => 'file 4 content',
            ],
            'file5.txt' => 'file 5 content',
        ];
        $this->createFileStructure([
            $source => $structure,
        ]);

        $basePath = $this->testFilePath;
        $source = $basePath . '/' . $source;
        $destination = $basePath . '/test_dst_dir';

        FileHelper::copyDirectory($source, $destination);

        $this->assertFileExists($destination, 'Destination directory does not exist!');

        $checker = function ($structure, $dstDirName) use (&$checker) {
            foreach ($structure as $name => $content) {
                if (is_array($content)) {
                    $checker($content, $dstDirName . '/' . $name);
                } else {
                    $fileName = $dstDirName . '/' . $name;
                    $this->assertFileExists($fileName);
                    $this->assertStringEqualsFile($fileName, $content, 'Incorrect file content!');
                }
            }
        };

        $checker($structure, $destination);
    }

    public function testCopyDirectoryNotRecursive(): void
    {
        $source = 'test_src_dir_not_rec';
        $structure = [
            'directory1' => [
                'file1.txt' => 'file 1 content',
                'file2.txt' => 'file 2 content',
            ],
            'directory2' => [
                'file3.txt' => 'file 3 content',
                'file4.txt' => 'file 4 content',
            ],
            'file5.txt' => 'file 5 content',
        ];
        $this->createFileStructure([
            $source => $structure,
        ]);

        $basePath = $this->testFilePath;
        $source = $basePath . '/' . $source;
        $destination = $basePath . '/' . 'test_dst_dir';

        FileHelper::copyDirectory($source, $destination, ['recursive' => false]);

        $this->assertFileExists($destination, 'Destination directory does not exist!');

        foreach ($structure as $name => $content) {
            $fileName = $destination . '/' . $name;

            if (is_array($content)) {
                $this->assertFileNotExists($fileName);
            } else {
                $this->assertFileExists($fileName);
                $this->assertStringEqualsFile($fileName, $content, 'Incorrect file content!');
            }
        }
    }

    /**
     * @depends testCopyDirectory
     */
    public function testCopyDirectoryPermissions(): void
    {
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        if ($isWindows) {
            $this->markTestSkipped('Skipping tests on Windows because fileperms() always return 0777.');
        }

        $source = 'test_src_dir';
        $subDirectory = 'test_sub_dir';
        $fileName = 'test_file.txt';
        $this->createFileStructure([
            $source => [
                $subDirectory => [],
                $fileName => 'test file content',
            ],
        ]);

        $basePath = $this->testFilePath;
        $source = $basePath . '/' . $source;
        $destination = $basePath . '/test_dst_dir';

        $directoryMode = 0755;
        $fileMode = 0755;
        $options = [
            'dirMode' => $directoryMode,
            'fileMode' => $fileMode,
        ];
        FileHelper::copyDirectory($source, $destination, $options);

        $this->assertFileMode($directoryMode, $destination, 'Destination directory has wrong mode!');
        $this->assertFileMode($directoryMode, $destination . '/' . $subDirectory, 'Copied sub directory has wrong mode!');
        $this->assertFileMode($fileMode, $destination . '/' . $fileName, 'Copied file has wrong mode!');
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/10710
     */
    public function testCopyDirectoryToItself(): void
    {
        $directoryName = 'test_dir';

        $this->createFileStructure([
            $directoryName => [],
        ]);

        $this->expectException(InvalidArgumentException::class);

        $directoryName = $this->testFilePath . '/test_dir';
        FileHelper::copyDirectory($directoryName, $directoryName);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/10710
     */
    public function testCopyDirToSubdirOfItself(): void
    {
        $this->createFileStructure([
            'data' => [],
            'backup' => ['data' => []],
        ]);

        $this->expectException(InvalidArgumentException::class);

        FileHelper::copyDirectory(
            $this->testFilePath . '/backup',
            $this->testFilePath . '/backup/data'
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/10710
     */
    public function testCopyDirToAnotherWithSameName(): void
    {
        $this->createFileStructure([
            'data' => [],
            'backup' => ['data' => []],
        ]);

        FileHelper::copyDirectory(
            $this->testFilePath . '/data',
            $this->testFilePath . '/backup/data'
        );
        $this->assertFileExists($this->testFilePath . '/backup/data');
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/10710
     */
    public function testCopyDirWithSameName(): void
    {
        $this->createFileStructure([
            'data' => [],
            'data-backup' => [],
        ]);

        FileHelper::copyDirectory(
            $this->testFilePath . '/data',
            $this->testFilePath . '/data-backup'
        );

        $this->assertTrue(true, 'no error');
    }

    public function testRemoveDirectory(): void
    {
        $dirName = 'test_dir_for_remove';
        $this->createFileStructure([
            $dirName => [
                'file1.txt' => 'file 1 content',
                'file2.txt' => 'file 2 content',
                'test_sub_dir' => [
                    'sub_dir_file_1.txt' => 'sub dir file 1 content',
                    'sub_dir_file_2.txt' => 'sub dir file 2 content',
                ],
            ],
        ]);

        $basePath = $this->testFilePath;
        $dirName = $basePath . '/' . $dirName;

        FileHelper::removeDirectory($dirName);

        $this->assertFileNotExists($dirName, 'Unable to remove directory!');

        // should be silent about non-existing directories
        FileHelper::removeDirectory($basePath . '/nonExisting');
    }

    public function testRemoveDirectorySymlinks1(): void
    {
        $dirName = 'remove-directory-symlinks-1';
        $this->createFileStructure([
            $dirName => [
                'file' => 'Symlinked file.',
                'directory' => [
                    'standard-file-1' => 'Standard file 1.',
                ],
                'symlinks' => [
                    'standard-file-2' => 'Standard file 2.',
                    'symlinked-file' => ['symlink', '../file'],
                    'symlinked-directory' => ['symlink', '../directory'],
                ],
            ],
        ]);

        $basePath = $this->testFilePath . '/' . $dirName . '/';
        $this->assertFileExists($basePath . 'file');
        $this->assertDirectoryExists($basePath . 'directory');
        $this->assertFileExists($basePath . 'directory/standard-file-1');
        $this->assertDirectoryExists($basePath . 'symlinks');
        $this->assertFileExists($basePath . 'symlinks/standard-file-2');
        $this->assertFileExists($basePath . 'symlinks/symlinked-file');
        $this->assertDirectoryExists($basePath . 'symlinks/symlinked-directory');
        $this->assertFileExists($basePath . 'symlinks/symlinked-directory/standard-file-1');

        FileHelper::removeDirectory($basePath . 'symlinks');

        $this->assertFileExists($basePath . 'file');
        $this->assertDirectoryExists($basePath . 'directory');
        $this->assertFileExists($basePath . 'directory/standard-file-1'); // symlinked directory still have it's file
        $this->assertDirectoryNotExists($basePath . 'symlinks');
        $this->assertFileNotExists($basePath . 'symlinks/standard-file-2');
        $this->assertFileNotExists($basePath . 'symlinks/symlinked-file');
        $this->assertDirectoryNotExists($basePath . 'symlinks/symlinked-directory');
        $this->assertFileNotExists($basePath . 'symlinks/symlinked-directory/standard-file-1');
    }

    public function testRemoveDirectorySymlinks2(): void
    {
        $dirName = 'remove-directory-symlinks-2';
        $this->createFileStructure([
            $dirName => [
                'file' => 'Symlinked file.',
                'directory' => [
                    'standard-file-1' => 'Standard file 1.',
                ],
                'symlinks' => [
                    'standard-file-2' => 'Standard file 2.',
                    'symlinked-file' => ['symlink', '../file'],
                    'symlinked-directory' => ['symlink', '../directory'],
                ],
            ],
        ]);

        $basePath = $this->testFilePath . '/' . $dirName . '/';
        $this->assertFileExists($basePath . 'file');
        $this->assertDirectoryExists($basePath . 'directory');
        $this->assertFileExists($basePath . 'directory/standard-file-1');
        $this->assertDirectoryExists($basePath . 'symlinks');
        $this->assertFileExists($basePath . 'symlinks/standard-file-2');
        $this->assertFileExists($basePath . 'symlinks/symlinked-file');
        $this->assertDirectoryExists($basePath . 'symlinks/symlinked-directory');
        $this->assertFileExists($basePath . 'symlinks/symlinked-directory/standard-file-1');

        FileHelper::removeDirectory($basePath . 'symlinks', ['traverseSymlinks' => true]);

        $this->assertFileExists($basePath . 'file');
        $this->assertDirectoryExists($basePath . 'directory');
        $this->assertFileNotExists($basePath . 'directory/standard-file-1'); // symlinked directory doesn't have it's file now
        $this->assertDirectoryNotExists($basePath . 'symlinks');
        $this->assertFileNotExists($basePath . 'symlinks/standard-file-2');
        $this->assertFileNotExists($basePath . 'symlinks/symlinked-file');
        $this->assertDirectoryNotExists($basePath . 'symlinks/symlinked-directory');
        $this->assertFileNotExists($basePath . 'symlinks/symlinked-directory/standard-file-1');
    }

    public function testFindFiles(): void
    {
        $dirName = 'test_dir';
        $this->createFileStructure([
            $dirName => [
                'file_1.txt' => 'file 1 content',
                'file_2.txt' => 'file 2 content',
                'test_sub_dir' => [
                    'file_1_1.txt' => 'sub dir file 1 content',
                    'file_1_2.txt' => 'sub dir file 2 content',
                ],
            ],
        ]);
        $basePath = $this->testFilePath;
        $dirName = $basePath . '/' . $dirName;
        $expectedFiles = [
            $dirName . '/file_1.txt',
            $dirName . '/file_2.txt',
            $dirName . '/test_sub_dir/file_1_1.txt',
            $dirName . '/test_sub_dir/file_1_2.txt',
        ];

        $foundFiles = FileHelper::findFiles($dirName);
        sort($expectedFiles);
        sort($foundFiles);
        $this->assertEquals($expectedFiles, $foundFiles);
    }

    /**
     * @depends testFindFiles
     */
    public function testFindFileFilter(): void
    {
        $dirName = 'test_dir';
        $passedFileName = 'passed.txt';
        $this->createFileStructure([
            $dirName => [
                $passedFileName => 'passed file content',
                'declined.txt' => 'declined file content',
            ],
        ]);
        $basePath = $this->testFilePath;
        $dirName = $basePath . '/' . $dirName;

        $options = [
            'filter' => function ($path) use ($passedFileName) {
                return $passedFileName === basename($path);
            },
        ];
        $foundFiles = FileHelper::findFiles($dirName, $options);
        $this->assertEquals([$dirName . '/' . $passedFileName], $foundFiles);
    }

    /**
     * @depends testFindFiles
     */
    public function testFindFilesRecursiveWithSymLink(): void
    {
        $dirName = 'test_dir';
        $this->createFileStructure([
            $dirName => [
                'theDir' => [
                    'file1' => 'abc',
                    'file2' => 'def',
                ],
                'symDir' => ['symlink', 'theDir'],
            ],
        ]);
        $dirName = $this->testFilePath . '/' . $dirName;

        $expected = [
            $dirName . '/symDir/file1',
            $dirName . '/symDir/file2',
            $dirName . '/theDir/file1',
            $dirName . '/theDir/file2',
        ];
        $result = FileHelper::findFiles($dirName);
        sort($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @depends testFindFiles
     */
    public function testFindFilesNotRecursive(): void
    {
        $dirName = 'test_dir';
        $this->createFileStructure([
            $dirName => [
                'theDir' => [
                    'file1' => 'abc',
                    'file2' => 'def',
                ],
                'symDir' => ['symlink', 'theDir'],
                'file3' => 'root',
            ],
        ]);
        $dirName = $this->testFilePath . '/' . $dirName;

        $expected = [
            $dirName . '/file3',
        ];
        $this->assertEquals($expected, FileHelper::findFiles($dirName, ['recursive' => false]));
    }

    /**
     * @depends testFindFiles
     */
    public function testFindFilesExclude(): void
    {
        $basePath = $this->testFilePath . '/';
        $directories = ['', 'one', 'one/two', 'three'];
        $files = array_fill_keys(array_map(function ($n) {
            return "a.$n";
        }, range(1, 8)), 'file contents');

        $tree = $files;
        $root = $files;
        $flat = [];
        foreach ($directories as $directory) {
            foreach ($files as $fileName => $contents) {
                $flat[] = rtrim($basePath . $directory, '/') . '/' . $fileName;
            }
            if ($directory === '') {
                continue;
            }
            $parts = explode('/', $directory);
            $last = array_pop($parts);
            $parent = array_pop($parts);
            $tree[$last] = $files;
            if ($parent !== null) {
                $tree[$parent][$last] = &$tree[$last];
            } else {
                $root[$last] = &$tree[$last];
            }
        }
        $this->createFileStructure($root);

        // range
        $foundFiles = FileHelper::findFiles($basePath, ['except' => ['a.[2-8]']]);
        sort($foundFiles);
        $expect = array_values(array_filter($flat, function ($p) {
            return substr($p, -3) === 'a.1';
        }));
        $this->assertEquals($expect, $foundFiles);

        // suffix
        $foundFiles = FileHelper::findFiles($basePath, ['except' => ['*.1']]);
        sort($foundFiles);
        $expect = array_values(array_filter($flat, function ($p) {
            return substr($p, -3) !== 'a.1';
        }));
        $this->assertEquals($expect, $foundFiles);

        // dir
        $foundFiles = FileHelper::findFiles($basePath, ['except' => ['/one']]);
        sort($foundFiles);
        $expect = array_values(array_filter($flat, function ($p) {
            return strpos($p, '/one') === false;
        }));
        $this->assertEquals($expect, $foundFiles);

        // directory contents
        $foundFiles = FileHelper::findFiles($basePath, ['except' => ['?*/a.1']]);
        sort($foundFiles);
        $expect = array_values(array_filter($flat, function ($p) {
            return substr($p, -11, 10) === 'one/two/a.' || (
                substr($p, -8) !== '/one/a.1' &&
                substr($p, -10) !== '/three/a.1'
            );
        }));
        $this->assertEquals($expect, $foundFiles);
    }

    /**
     * @depends testFindFilesExclude
     */
    public function testFindFilesCaseSensitive(): void
    {
        $directory = 'test_dir';
        $this->createFileStructure([
            $directory => [
                'lower.txt' => 'lower case filename',
                'upper.TXT' => 'upper case filename',
            ],
        ]);
        $basePath = $this->testFilePath;
        $directory = $basePath . '/' . $directory;

        $options = [
            'except' => ['*.txt'],
            'caseSensitive' => false,
        ];
        $foundFiles = FileHelper::findFiles($directory, $options);
        $this->assertCount(0, $foundFiles);

        $options = [
            'only' => ['*.txt'],
            'caseSensitive' => false,
        ];
        $foundFiles = FileHelper::findFiles($directory, $options);
        $this->assertCount(2, $foundFiles);
    }

    public function testGetMimeTypeByExtension(): void
    {
        $magicFile = $this->testFilePath . '/mime_type_test.php';
        $mimeTypeMap = [
            'txa' => 'application/json',
            'txb' => 'another/mime',
        ];
        $magicFileContent = '<?php return ' . var_export($mimeTypeMap, true) . ';';
        file_put_contents($magicFile, $magicFileContent);

        foreach ($mimeTypeMap as $extension => $mimeType) {
            $fileName = 'test.' . $extension;
            $this->assertNull(FileHelper::getMimeTypeByExtension($fileName));
            $this->assertEquals($mimeType, FileHelper::getMimeTypeByExtension($fileName, $magicFile));
        }
    }

    public function testGetMimeType(): void
    {
        $file = $this->testFilePath . '/mime_type_test.txt';
        file_put_contents($file, 'some text');
        $this->assertEquals('text/plain', FileHelper::getMimeType($file));

        // see http://stackoverflow.com/questions/477816/what-is-the-correct-json-content-type
        // JSON/JSONP should not use text/plain - see http://jibbering.com/blog/?p=514
        // with "fileinfo" extension enabled, returned MIME is not quite correctly "text/plain"
        // without "fileinfo" it falls back to getMimeTypeByExtension() and returns application/json
        $file = $this->testFilePath . '/mime_type_test.json';
        file_put_contents($file, '{"a": "b"}');
        $this->assertContains(FileHelper::getMimeType($file), ['application/json', 'text/plain']);
    }

    public function testNormalizePath(): void
    {
        $this->assertEquals('/a/b', FileHelper::normalizePath('//a\\b/'));
        $this->assertEquals('/b/c', FileHelper::normalizePath('/a/../b/c'));
        $this->assertEquals('/c', FileHelper::normalizePath('/a\\b/../..///c'));
        $this->assertEquals('/c', FileHelper::normalizePath('/a/.\\b//../../c'));
        $this->assertEquals('c', FileHelper::normalizePath('/a/.\\b/../..//../c'));
        $this->assertEquals('../c', FileHelper::normalizePath('//a/.\\b//..//..//../../c'));

        // relative paths
        $this->assertEquals('.', FileHelper::normalizePath('.'));
        $this->assertEquals('.', FileHelper::normalizePath('./'));
        $this->assertEquals('a', FileHelper::normalizePath('.\\a'));
        $this->assertEquals('a/b', FileHelper::normalizePath('./a\\b'));
        $this->assertEquals('.', FileHelper::normalizePath('./a\\../'));
        $this->assertEquals('../../a', FileHelper::normalizePath('../..\\a'));
        $this->assertEquals('../../a', FileHelper::normalizePath('../..\\a/../a'));
        $this->assertEquals('../../b', FileHelper::normalizePath('../..\\a/../b'));
        $this->assertEquals('../a', FileHelper::normalizePath('./..\\a'));
        $this->assertEquals('../a', FileHelper::normalizePath('././..\\a'));
        $this->assertEquals('../a', FileHelper::normalizePath('./..\\a/../a'));
        $this->assertEquals('../b', FileHelper::normalizePath('./..\\a/../b'));

        // Windows file system may have paths for network shares that start with two backslashes. These two backslashes
        // should not be touched.
        // https://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        // https://github.com/yiisoft/yii2/issues/13034
        $this->assertEquals('\\\\server/share/path/file', FileHelper::normalizePath('\\\\server\share\path\file', '\\'));

    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/3393
     *
     * @depends testCopyDirectory
     * @depends testFindFiles
     */
    public function testCopyDirectoryExclude(): void
    {
        $source = 'test_src_dir';
        $textFiles = [
            'file1.txt' => 'text file 1 content',
            'file2.txt' => 'text file 2 content',
        ];
        $dataFiles = [
            'file1.dat' => 'data file 1 content',
            'file2.dat' => 'data file 2 content',
        ];
        $this->createFileStructure([
            $source => array_merge($textFiles, $dataFiles),
        ]);

        $basePath = $this->testFilePath;
        $source = $basePath . '/' . $source;
        $destination = $basePath . '/test_dst_dir';

        FileHelper::copyDirectory($source, $destination, ['only' => ['*.dat']]);

        $this->assertFileExists($destination, 'Destination directory does not exist!');
        $copiedFiles = FileHelper::findFiles($destination);
        $this->assertCount(2, $copiedFiles, 'wrong files count copied');

        foreach ($dataFiles as $name => $content) {
            $fileName = $destination . '/' . $name;
            $this->assertFileExists($fileName);
            $this->assertStringEqualsFile($fileName, $content, 'Incorrect file content!');
        }
    }

    private function setupCopyEmptyDirectoriesTest(): array
    {
        $source = 'test_empty_src_dir';
        $this->createFileStructure([
            $source => [
                'dir1' => [
                    'file1.txt' => 'file1',
                    'file2.txt' => 'file2',
                ],
                'dir2' => [
                    'file1.log' => 'file1',
                    'file2.log' => 'file2',
                ],
                'dir3' => [],
            ],
        ]);

        return [
            $this->testFilePath, // basePath
            $this->testFilePath . '/' . $source,
        ];
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/9669
     *
     * @depends testCopyDirectory
     * @depends testFindFiles
     */
    public function testCopyDirectoryEmptyDirectories(): void
    {
        [$basePath, $source] = $this->setupCopyEmptyDirectoriesTest();

        // copy with empty directories
        $destination = $basePath . '/test_empty_dst_dir';
        FileHelper::copyDirectory($source, $destination, ['only' => ['*.txt'], 'copyEmptyDirectories' => true]);

        $this->assertFileExists($destination, 'Destination directory does not exist!');
        $copiedFiles = FileHelper::findFiles($destination);
        $this->assertCount(2, $copiedFiles, 'wrong files count copied');

        $this->assertFileExists($destination . '/dir1');
        $this->assertFileExists($destination . '/dir1/file1.txt');
        $this->assertFileExists($destination . '/dir1/file2.txt');
        $this->assertFileExists($destination . '/dir2');
        $this->assertFileExists($destination . '/dir3');
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/9669
     *
     * @depends testCopyDirectory
     * @depends testFindFiles
     */
    public function testCopyDirectoryNoEmptyDirectories(): void
    {
        [$basePath, $source] = $this->setupCopyEmptyDirectoriesTest();

        // copy without empty directories
        $destination = $basePath . '/test_empty_dst_dir2';
        FileHelper::copyDirectory($source, $destination, ['only' => ['*.txt'], 'copyEmptyDirectories' => false]);

        $this->assertFileExists($destination, 'Destination directory does not exist!');
        $copiedFiles = FileHelper::findFiles($destination);
        $this->assertCount(2, $copiedFiles, 'wrong files count copied');

        $this->assertFileExists($destination . '/dir1');
        $this->assertFileExists($destination . '/dir1/file1.txt');
        $this->assertFileExists($destination . '/dir1/file2.txt');
        $this->assertFileNotExists($destination . '/dir2');
        $this->assertFileNotExists($destination . '/dir3');
    }

    public function testFindDirectories(): void
    {
        $dirName = 'test_dir';
        $this->createFileStructure([
            $dirName => [
               'test_sub_dir' => [
                    'file_1.txt' => 'sub dir file 1 content',
                ],
                'second_sub_dir' => [
                    'file_1.txt' => 'sub dir file 2 content',
                ],
            ],
        ]);
        $basePath = $this->testFilePath;
        $dirName = $basePath . '/' . $dirName;
        $expectedFiles = [
            $dirName . '/test_sub_dir',
            $dirName . '/second_sub_dir'
        ];

        $foundFiles = FileHelper::findDirectories($dirName);
        sort($expectedFiles);
        sort($foundFiles);
        $this->assertEquals($expectedFiles, $foundFiles);

        // filter
        $expectedFiles = [
            $dirName . '/second_sub_dir'
        ];
        $options = [
            'filter' => function ($path) {
                return 'second_sub_dir' === basename($path);
            },
        ];
        $foundFiles = FileHelper::findDirectories($dirName, $options);
        sort($expectedFiles);
        sort($foundFiles);
        $this->assertEquals($expectedFiles, $foundFiles);

        // except
        $expectedFiles = [
            $dirName . '/second_sub_dir'
        ];
        $options = [
            'except' => ['test_sub_dir'],
        ];
        $foundFiles = FileHelper::findDirectories($dirName, $options);
        sort($expectedFiles);
        sort($foundFiles);
        $this->assertEquals($expectedFiles, $foundFiles);
    }
}
