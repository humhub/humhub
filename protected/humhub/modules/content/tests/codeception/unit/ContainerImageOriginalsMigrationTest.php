<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content;

use humhub\components\assets\AssetImage;
use humhub\modules\user\models\User;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use League\Flysystem\Filesystem;
use m261118_080000_container_image_originals;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\imagine\Image;

/**
 * Up to 1.18 a container image consisted of the cropped display image `<guid>.jpg` and the
 * uncropped original `<guid>_org.jpg`; since 1.19 `<guid>.jpg` is the source of all variants.
 */
class ContainerImageOriginalsMigrationTest extends HumHubDbTestCase
{
    private const PROFILE = 'profile';
    private const BANNER = 'banner';

    private Filesystem $fs;

    protected function _before()
    {
        parent::_before();

        require_once Yii::getAlias('@humhub/modules/content/migrations/m261118_080000_container_image_originals.php');
        $this->fs = Yii::$app->fs->getDataMount();

        // Removes the source file, all variants and an original left by a previous run
        $this->getImage(self::PROFILE)->delete();
        $this->getImage(self::BANNER)->delete();
    }

    public function testOriginalBecomesSourceAndCroppedImageStaysDefaultVariant()
    {
        $cropped = $this->createJpeg(150, 150, '#ff0000');
        $original = $this->createJpeg(400, 600, '#0000ff');
        $image = $this->writeImage(self::PROFILE, $cropped, $original);

        // Variants rendered by 1.19 before the update, from the small cropped image
        $image->getUrl();
        $image->getUrl([]);

        $this->migrate();

        $this->assertSame($original, $this->fs->read($image->file));
        $this->assertFalse($this->fs->fileExists($this->getOriginalFile($image)));

        // Displayed unchanged in the default size ...
        $image->getUrl();
        $this->assertSame($cropped, $this->fs->read($this->getVariantFile($image, $image->defaultOptions)));

        // ... while the full-size view shows the original again
        $image->getUrl([]);
        $this->assertSame([400, 600], $this->getImageSize($this->getVariantFile($image, [])));
    }

    public function testBannerOriginalBecomesSourceAndCroppedImageStaysDefaultVariant()
    {
        $cropped = $this->createJpeg(1134, 192, '#ff0000');
        $original = $this->createJpeg(1600, 900, '#0000ff');
        $image = $this->writeImage(self::BANNER, $cropped, $original);

        $this->migrate();

        $this->assertSame($original, $this->fs->read($image->file));
        $this->assertSame($cropped, $this->fs->read($this->getVariantFile($image, $image->defaultOptions)));
        $this->assertFalse($this->fs->fileExists($this->getOriginalFile($image)));
    }

    public function testDeletedImageIsNotRestored()
    {
        $image = $this->writeImage(self::PROFILE, null, $this->createJpeg(400, 600, '#0000ff'));

        $this->migrate();

        $this->assertFalse($this->fs->fileExists($image->file));
    }

    public function testInterruptedRunIsCompleted()
    {
        $cropped = $this->createJpeg(150, 150, '#ff0000');
        $original = $this->createJpeg(400, 600, '#0000ff');
        $image = $this->writeImage(self::PROFILE, $cropped, $original);
        $this->migrate();

        // The original was already copied to the source file, but not yet removed
        $this->fs->write($this->getOriginalFile($image), $original);
        $this->migrate();

        $this->assertSame($original, $this->fs->read($image->file));
        $this->assertSame($cropped, $this->fs->read($this->getVariantFile($image, $image->defaultOptions)));
        $this->assertFalse($this->fs->fileExists($this->getOriginalFile($image)));
    }

    public function testOriginalOfUnknownContainerIsIgnored()
    {
        $file = '/profile_image/00000000-0000-0000-0000-000000000000_org.jpg';
        $this->fs->write($file, $this->createJpeg(400, 600, '#0000ff'));

        $this->migrate();

        $this->assertTrue($this->fs->fileExists($file));
        $this->fs->delete($file);
    }

    private function migrate(): void
    {
        ob_start();
        (new m261118_080000_container_image_originals())->up();
        ob_end_clean();
    }

    /**
     * Writes the files of an image directly, as 1.18 stored them.
     *
     * @return AssetImage a new instance, as an instance caches whether its file exists
     */
    private function writeImage(string $type, ?string $source, ?string $original): AssetImage
    {
        $image = $this->getImage($type);
        if ($source !== null) {
            $this->fs->write($image->file, $source);
        }
        if ($original !== null) {
            $this->fs->write($this->getOriginalFile($image), $original);
        }

        return $this->getImage($type);
    }

    private function getImage(string $type): AssetImage
    {
        $user = User::findOne(['id' => 1]);

        return $type === self::BANNER ? $user->getBannerImage() : $user->getImage();
    }

    private function getOriginalFile(AssetImage $image): string
    {
        return $image->path . '/' . pathinfo($image->file, PATHINFO_FILENAME) . '_org.jpg';
    }

    private function getVariantFile(AssetImage $image, array $options): string
    {
        ksort($options);

        return $image->path . '/' . pathinfo($image->file, PATHINFO_FILENAME)
            . '_' . hash('xxh32', json_encode($options)) . '.jpg';
    }

    private function getImageSize(string $file): array
    {
        $size = getimagesizefromstring($this->fs->read($file));

        return [$size[0], $size[1]];
    }

    private function createJpeg(int $width, int $height, string $color): string
    {
        return Image::getImagine()
            ->create(new Box($width, $height), (new RGB())->color($color))
            ->get('jpeg');
    }
}
