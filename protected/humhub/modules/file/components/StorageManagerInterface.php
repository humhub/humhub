<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\components;

/**
 * StorageManagerInterface
 * 
 * @version 1.2
 * @author Luke
 */
interface StorageManagerInterface
{

    public function get($variant = null);

    public function set(\yii\web\UploadedFile $file, $variant = null);

    public function setContent($content, $variant = null);

    public function delete($variant = null);

    public function setFile(\humhub\modules\file\models\File $file);
}
