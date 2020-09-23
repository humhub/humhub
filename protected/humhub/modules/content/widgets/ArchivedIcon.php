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

        return static::get(static::ICON_NAME,  ['htmlOptions' => [
            'class' => 'tt archived',
            'title' => Yii::t('ContentModule.base', 'Archived')
        ]]);
    }

}
