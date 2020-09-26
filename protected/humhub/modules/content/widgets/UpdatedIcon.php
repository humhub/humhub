<?php


namespace humhub\modules\content\widgets;


use humhub\modules\ui\icon\widgets\Icon;
use Yii;

class UpdatedIcon extends Icon
{
    const ICON_NAME = 'clock-o';

    public static function getByDated($updateDate)
    {
        return static::get(static::ICON_NAME)
            ->tooltip(Yii::t('ContentModule.base', 'Last updated {time}', ['time' => Yii::$app->formatter->asDateTime($updateDate)]));
    }

}
