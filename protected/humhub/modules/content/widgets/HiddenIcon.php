<?php


namespace humhub\modules\content\widgets;


use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\ui\icon\widgets\Icon;
use Yii;

/**
 * Usage:
 *
 * ```php
 * <?= HiddenIcon::getByModel($model) ?>
 * ```
 *
 * @since 1.14
 */
class HiddenIcon extends Icon
{

    /**
     * Returns a visibility icon with tooltip for the given $model.
     *
     * @param ContentActiveRecord $model
     * @return Icon|string
     * @throws \Throwable
     */
    public static function getByModel(ContentActiveRecord $model)
    {
        if ($model->content->hidden) {
            return static::get('eye-slash')->tooltip(Yii::t('ContentModule.base', 'Hidden'));
        }

        return '';
    }
}
