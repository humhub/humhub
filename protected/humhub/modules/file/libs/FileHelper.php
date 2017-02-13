<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\libs\Html;
use yii\helpers\Url;
use humhub\modules\file\models\File;

/**
 * FileHelper
 *
 * @since 1.2
 * @author Luke
 */
class FileHelper extends \yii\helpers\FileHelper
{

    /**
     * Checks if given fileName has a extension
     * 
     * @param string $fileName the filename
     * @return boolean has extension
     */
    public static function hasExtension($fileName)
    {
        return (strpos($fileName, ".") !== false);
    }

    /**
     * Returns the extension of a file
     * 
     * @param string|File $fileName the filename or File model
     * @return string the extension
     */
    public static function getExtension($fileName)
    {
        if ($fileName instanceof File) {
            $fileName = $fileName->file_name;
        }

        $fileParts = pathinfo($fileName);
        if (isset($fileParts['extension'])) {
            return $fileParts['extension'];
        }
        return '';
    }

    /**
     * Creates a file with options
     * 
     * @param \humhub\modules\file\models\File $file
     */
    public static function createLink($file, $options = [], $htmlOptions = [])
    {
        $label = (isset($htmlOptions['label'])) ? $htmlOptions['label'] : Html::encode($file->fileName);

        $htmlOptions = array_merge($htmlOptions, ['data-target' => '#globalModal']);
        return Html::a($label, Url::to(['/file/view', 'guid' => $file->guid]), $htmlOptions);
    }

    /**
     * Determines the content container of a File record
     * 
     * @param File $file
     * @return \humhub\modules\content\components\ContentContainerActiveRecord the content container or null
     */
    public static function getContentContainer($file)
    {
        $relation = $file->getPolymorphicRelation();

        if ($relation !== null && $relation instanceof \humhub\modules\content\components\ContentActiveRecord) {
            if ($relation->content->container !== null) {
                return $relation->content->container;
            }
        }

        return null;
    }

}
