<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\handler;

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
        return [
            'label' => Yii::t('FileModule.base', 'Download') . ' <small>(' . Yii::$app->formatter->asShortSize($this->file->size, 1) . ')</small>',
            'href' => self::getUrl($this->file),
            'target' => '_blank',
        ];
    }

    public static function getUrl($file, $download = 0)
    {
        return Url::to(['/file/file/download', 'guid' => $file->guid, 'download' => $download]);
    }

}
