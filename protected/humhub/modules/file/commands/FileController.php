<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\commands;

use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\models\FileHistory;
use Yii;
use yii\console\widgets\Table;

/**
 * Management of uploaded files
 *
 * @since 1.7
 */
class FileController extends \yii\console\Controller
{

    /**
     * Overview of uploaded files and automatically generated variants.
     */
    public function actionIndex()
    {
        $this->stdout("*** File module console\n\n");

        $fileSize = 0;
        $fileSizes = [];
        /** @var File $file */
        foreach (File::find()->each() as $file) {
            if (!is_file($file->store->get())) {
                continue;
            }

            $fileSize += filesize($file->store->get());
            foreach ($file->store->getVariants() as $variant) {
                if (!isset($fileSizes[$variant])) {
                    $fileSizes[$variant] = 0;
                }
                $fileSizes[$variant] += filesize($file->store->get($variant));
            }
        }

        echo Table::widget(['rows' => [
            ['Total number of uploaded files', File::find()->count()],
            ['Size', Yii::$app->formatter->asShortSize($fileSize)],

        ]]);

        $this->stdout("\nAutomatically generated file variants:\n");

        $table = [];
        foreach ($fileSizes as $v => $s) {
            $table[] = [$v, Yii::$app->formatter->asShortSize($s)];
        }
        echo Table::widget(['headers' => ['Variant', 'Size'], 'rows' => $table]);
    }

    /**
     * Deletes all automatically generated file variants (previews, converted versions).
     */
    public function actionDeleteVariants()
    {
        $this->stdout("*** File module console\n\n");
        $this->stdout('Deleting automatically created file variants:');

        /** @var File $file */
        foreach (File::find()->each() as $file) {
            foreach ($file->store->getVariants([FileHistory::VARIANT_PREFIX . '*']) as $variant) {
                $file->store->delete($variant);
                $this->stdout('.');
            }
        }

        $this->stdout("OK!\n\n");
    }


    /**
     * Scales down already uploaded images to the maximum dimensions and quality.
     */
    public function actionDownscaleImages()
    {
        $this->stdout("*** File module console\n\n");
        $this->stdout('Downscaling uploaded files:');

        /** @var File $file */
        foreach (File::find()->each() as $file) {
            ImageHelper::downscaleImage($file);
            $this->stdout('.');
        }

        $this->stdout("OK!\n\n");
    }
}
