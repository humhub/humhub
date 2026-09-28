<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\fs;

use humhub\components\fs\LocalMountConfig;
use League\Flysystem\Visibility;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\FileHelper;

class LocalMountConfigTest extends HumHubDbTestCase
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
        $this->umask = umask(022);
    }

    protected function _after()
    {
        umask($this->umask);
        FileHelper::removeDirectory($this->root);

        parent::_after();
    }

    private function mount(array $config): LocalMountConfig
    {
        return Yii::createObject(['class' => LocalMountConfig::class, 'path' => $this->root] + $config);
    }

    private function assertMode(int $expected, string $path)
    {
        clearstatcache();
        $this->assertSame(
            sprintf('%04o', $expected),
            sprintf('%04o', fileperms($this->root . DIRECTORY_SEPARATOR . $path) & 0777),
            "Mode of $path",
        );
    }

    /**
     * Public and private entries get the same Yii2 modes, so a directory created by one process (e.g. the web
     * server) stays writable by another one of the same group (e.g. the CLI), whatever the umask.
     */
    public function testDefaultModesMatchYii()
    {
        $fs = $this->mount([])->getFileSystem();

        $fs->write('public/file.txt', 'test', [
            'visibility' => Visibility::PUBLIC,
            'directory_visibility' => Visibility::PUBLIC,
        ]);
        $fs->write('private/file.txt', 'test', [
            'visibility' => Visibility::PRIVATE,
            'directory_visibility' => Visibility::PRIVATE,
        ]);
        $fs->write('default/file.txt', 'test');

        $this->assertMode(0775, '');
        foreach (['public', 'private', 'default'] as $dir) {
            $this->assertMode(0775, $dir);
        }
        $this->assertMode(0664, 'public/file.txt');
        $this->assertMode(0664, 'private/file.txt');
    }

    public function testModesAreConfigurable()
    {
        $fs = $this->mount(['dirMode' => 0770, 'fileMode' => 0660])->getFileSystem();

        $fs->write('a/file.txt', 'test', ['visibility' => Visibility::PUBLIC]);

        $this->assertMode(0770, '');
        $this->assertMode(0770, 'a');
        $this->assertMode(0660, 'a/file.txt');
    }
}
