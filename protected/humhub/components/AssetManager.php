<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use yii\helpers\FileHelper;

/**
 * AssetManager
 *
 * @inheritdoc
 * @author Luke
 */
class AssetManager extends \yii\web\AssetManager
{

    /**
     * Clears all currently published assets
     */
    public function clear()
    {
        if ($this->basePath == '') {
            return;
        }

        foreach (scandir($this->basePath) as $file) {
            if (substr($file, 0, 1) === '.') {
                continue;
            }
            FileHelper::removeDirectory($this->basePath . DIRECTORY_SEPARATOR . $file);
        }
    }

}
