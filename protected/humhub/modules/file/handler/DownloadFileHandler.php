<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

use humhub\modules\file\models\File;
use humhub\modules\file\widgets\FileDownload;
use Yii;
use yii\helpers\Url;

/**
 * DownloadFileHandler provides the download link for a file
 *
 * @since 1.2
 * @author Luke
 */
class DownloadFileHandler extends BaseFileHandler
{

    /**
     * @inheritdoc
     */
    public $position = self::POSITION_TOP;

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return array_merge(FileDownload::getFileDataAttributes($this->file), [
            'label' => Yii::t('FileModule.base', 'Download') . FileDownload::getFileSizeString($this->file),
            'href' => self::getUrl($this->file),
            'target' => '_blank',
        ]);
    }

    public static function getUrl(File $file, $download = 0, $scheme = false)
    {
        if ($file === null) {
            return '';
        }

        return $file->getUrl(['download' => $download], $scheme);
    }

}
