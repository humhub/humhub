<?php

use humhub\components\Migration;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentContainer;
use League\Flysystem\Visibility;

/**
 * Makes the originals of profile and banner images uploaded before 1.19 their source again.
 *
 * Up to 1.18 such an image consisted of `<guid>.jpg`, the cropped image in its display size, and
 * `<guid>_org.jpg`, the uncropped original. Since 1.19 `<guid>.jpg` is the source of all variants,
 * so the full-size view showed only the small cropped image, and cropping it again made it
 * blurry. As every crop or upload since 1.19 removes the original, an existing original means
 * the image is unchanged since 1.18.
 *
 * The original becomes the source again, and the cropped image is kept as the variant of the
 * default options, so it is displayed unchanged. Note: this variant cannot be regenerated from
 * the source - if the default options change, the image shows the center of its original.
 */
class m261118_080000_container_image_originals extends Migration
{
    private const FILESYSTEM_OPTIONS = [
        'visibility' => Visibility::PUBLIC,
        'directory_visibility' => Visibility::PUBLIC,
    ];

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $fs = Yii::$app->fs->getDataMount();
        $count = 0;

        foreach (['/profile_image' => 'getImage', '/profile_image/banner' => 'getBannerImage'] as $path => $getImage) {
            $fileNames = [];
            foreach ($fs->listContents($path, false) as $item) {
                if ($item->isFile()) {
                    $fileNames[] = basename($item->path());
                }
            }

            foreach ($fileNames as $fileName) {
                if (!preg_match('/^(.+)_org\.jpg$/', $fileName, $matches)) {
                    continue;
                }

                $guid = $matches[1];
                $originalFile = $path . '/' . $fileName;
                try {
                    $container = ContentContainer::findRecord($guid);
                    if (!$container instanceof ContentContainerActiveRecord) {
                        continue;
                    }

                    $image = $container->$getImage();
                    if ($image->file !== $path . '/' . $guid . '.jpg' || !$fs->fileExists($image->file)) {
                        // Replaced by a module (see ContentContainerActiveRecord::EVENT_INIT_PROFILE_IMAGE), or deleted
                        continue;
                    }

                    $cropped = $fs->read($image->file);
                    $original = $fs->read($originalFile);

                    // Otherwise an interrupted run already made the original the source
                    if ($cropped !== $original) {
                        // Variants rendered since 1.19 from the cropped image
                        foreach (preg_grep('/^' . preg_quote($guid, '/') . '_[0-9a-f]{8}\.jpg$/', $fileNames) as $variant) {
                            $fs->delete($path . DIRECTORY_SEPARATOR . $variant);
                            Yii::$app->assetManager->unpublishAssetImage($image, $path . DIRECTORY_SEPARATOR . $variant);
                        }

                        // Same file name as AssetImage::getUrl() uses for the default options
                        $options = $image->defaultOptions;
                        ksort($options);
                        $fs->write(
                            $path . DIRECTORY_SEPARATOR . $guid . '_' . hash('xxh32', json_encode($options)) . '.jpg',
                            $cropped,
                            self::FILESYSTEM_OPTIONS,
                        );
                        $fs->write($image->file, $original, self::FILESYSTEM_OPTIONS);
                    }

                    $fs->delete($originalFile);
                    $count++;
                } catch (Throwable $e) {
                    Yii::error('Could not make ' . $originalFile . ' the source of its image: ' . $e->getMessage(), 'content');
                }
            }
        }

        if ($count > 0) {
            echo "    > made the original the source of $count profile and banner images\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        echo "m261118_080000_container_image_originals cannot be reverted.\n";

        return false;
    }
}
