<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\fs;

use humhub\components\fs\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\FileHelper;

class LocalFilesystemAdapterTest extends HumHubDbTestCase
{
    private string $root;
    private int $umask;

    protected function _before()
    {
        parent::_before();

        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('Unix permissions are not supported on Windows.');
        }

        $this->root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'humhub-fs-' . uniqid();
        // The usual umask of web servers and shells, which reduces the mode given to mkdir() to 0755
        $this->umask = umask(022);
    }

    protected function _after()
    {
        umask($this->umask);
        FileHelper::removeDirectory($this->root);

        parent::_after();
    }

    private function fs(): Filesystem
    {
        return new Filesystem(new LocalFilesystemAdapter(
            $this->root,
            new PortableVisibilityConverter(0664, 0660, 0775, 0770, Visibility::PUBLIC),
        ));
    }

    private function assertMode(int $expected, string $path)
    {
        clearstatcache();
        $this->assertSame(
            sprintf('%05o', $expected),
            sprintf('%05o', fileperms($this->root . DIRECTORY_SEPARATOR . $path) & 07777),
            "Mode of $path",
        );
    }

    public function testDirectoryModeIsAppliedRegardlessOfUmask()
    {
        $this->fs()->write('a/b/file.txt', 'test', [
            'visibility' => Visibility::PUBLIC,
            'directory_visibility' => Visibility::PUBLIC,
        ]);

        $this->assertMode(0775, '');
        $this->assertMode(0775, 'a');
        $this->assertMode(0775, 'a/b');
        $this->assertMode(0664, 'a/b/file.txt');
    }

    public function testPrivateVisibilityUsesPrivateModes()
    {
        $this->fs()->writeStream('a/b/file.txt', fopen('php://memory', 'r'), [
            'visibility' => Visibility::PRIVATE,
            'directory_visibility' => Visibility::PRIVATE,
        ]);

        $this->assertMode(0770, 'a');
        $this->assertMode(0770, 'a/b');
        $this->assertMode(0660, 'a/b/file.txt');
    }

    public function testCreateDirectoryAppliesModeToNewAndExistingDirectories()
    {
        $fs = $this->fs();

        $fs->createDirectory('a/b', ['visibility' => Visibility::PRIVATE]);
        $this->assertMode(0770, 'a');
        $this->assertMode(0770, 'a/b');

        // Existing directory: only the mode is (re)applied
        $fs->createDirectory('a/b', ['visibility' => Visibility::PUBLIC]);
        $this->assertMode(0770, 'a');
        $this->assertMode(0775, 'a/b');
    }

    public function testSetgidBitInheritedFromParentIsKept()
    {
        mkdir($this->root);
        if (!chmod($this->root, 02775) || (fileperms($this->root) & 02000) === 0) {
            $this->markTestSkipped('setgid cannot be set on ' . $this->root);
        }

        $this->fs()->write('a/b/file.txt', 'test', ['visibility' => Visibility::PUBLIC]);

        $this->assertMode(02775, 'a');
        $this->assertMode(02775, 'a/b');
    }

    public function testExistingDirectoriesAreNotChanged()
    {
        mkdir($this->root . '/a', 0700, true);
        chmod($this->root, 0700);
        chmod($this->root . '/a', 0700);

        $this->fs()->write('a/b/file.txt', 'test', ['visibility' => Visibility::PUBLIC]);

        $this->assertMode(0700, '');
        $this->assertMode(0700, 'a');
        $this->assertMode(0775, 'a/b');
    }

    public function testFailsWithFlysystemExceptionWhenDirectoryCannotBeCreated()
    {
        $fs = $this->fs();
        $fs->write('file.txt', 'test');

        $this->expectException(UnableToCreateDirectory::class);
        $fs->write('file.txt/sub/file.txt', 'test');
    }

    public function testCreateDirectoryFailsWithFlysystemExceptionWhenDirectoryCannotBeCreated()
    {
        $fs = $this->fs();
        $fs->write('file.txt', 'test');

        $this->expectException(UnableToCreateDirectory::class);
        $fs->createDirectory('file.txt/sub');
    }
}
