<?php


namespace humhub\modules\content\widgets;


use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\ui\icon\widgets\Icon;
use Yii;

class ArchivedIcon extends Icon
{
    const ICON_NAME = 'archive';

    public static function getByModel(ContentActiveRecord $model)
    {
        if(!$model->content->isArchived()) {
            return '';
        }

        return static::get(static::ICON_NAME)
            ->tooltip(Yii::t('ContentModule.base', 'Archived'), Yii::t('ContentModule.aria', 'This content is archived'));
    }

}
