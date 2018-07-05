<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\file\widgets;

use humhub\libs\MimeHelper;
use humhub\modules\file\models\File;
use humhub\widgets\Button;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class FileDownload extends Button
{
    public function file(File $file, $withIcon = true, $showSize = true, $download = false, $scheme = false)
    {
        if($withIcon) {
            $mimeIconClass = MimeHelper::getMimeIconClassByExtension($file);
            $this->icon(Html::tag('i', '', ['class' => 'mime '.$mimeIconClass, 'style' => 'width:10px;height:10px;']), false, true);
        }

        if($showSize) {
            $this->text .= static::getFileSizeString($file);
        }

        $this->link(static::getUrl($file, $download, $scheme));
        $this->options(static::getFileDataAttributes($file));

        return $this;
    }

    public static function getFileSizeString(File $file)
    {
        return ' <small>('.Yii::$app->formatter->asShortSize($file->size, 1).')</small>';
    }

    public static function getFileDataAttributes(File $file)
    {
        return [
            'data-pjax-prevent' => true,
            'data-file-download' => true,
            'data-file-url' =>  Url::to(['/file/file/download', 'guid' => $file->guid, 'download' => true], true),
            'data-file-name' => $file->file_name,
            'data-file-mime' => $file->mime_type,
        ];
    }

    public static function getUrl(File $file, $download, $scheme)
    {
        return Url::to(['/file/file/download', 'guid' => $file->guid, 'download' => $download], $scheme);
    }

}
