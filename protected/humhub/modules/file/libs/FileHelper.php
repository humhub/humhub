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
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\file\handler\DownloadFileHandler;

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
     * @since 1.2
     * @param \humhub\modules\file\models\File $file
     * @return string the rendered HTML link
     */
    public static function createLink($file, $options = [], $htmlOptions = [])
    {
        $label = (isset($htmlOptions['label'])) ? $htmlOptions['label'] : Html::encode($file->fileName);

        $fileHandlers = FileHandlerCollection::getByType([FileHandlerCollection::TYPE_VIEW, FileHandlerCollection::TYPE_EXPORT, FileHandlerCollection::TYPE_EDIT, FileHandlerCollection::TYPE_IMPORT], $file);
        if (count($fileHandlers) === 1 && $fileHandlers[0] instanceof DownloadFileHandler) {
            $htmlOptions['target'] = '_blank';
            return Html::a($label, Url::to(['/file/file/download', 'guid' => $file->guid]), $htmlOptions);
        }

        $htmlOptions = array_merge($htmlOptions, ['data-target' => '#globalModal']);
        return Html::a($label, Url::to(['/file/view', 'guid' => $file->guid]), $htmlOptions);
    }

    /**
     * Determines the content container of a File record
     * 
     * @since 1.2
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
