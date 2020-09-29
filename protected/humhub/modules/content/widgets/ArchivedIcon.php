<?php


namespace humhub\modules\content\widgets;


use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\ui\icon\widgets\Icon;
use Yii;
use yii\base\Exception;

/**
 * Can be used to render an archive icon for archived content.
 *
 * Usage:
 *
 * ```php
 * <?= ArchivedIcon::getByModel($myModel) ?>
 * ```
 * @package humhub\modules\content\widgets
 * @since 1.7
 */
class ArchivedIcon extends Icon
{
    /**
     * The icon name used for rendering
     */
    const ICON_NAME = 'archive';

    /**
     * Renders an archive icon with tooltip for archived ContentActiveRecord models.
     * This function will return an empty string if the model was not archived.
     *
     * @param ContentActiveRecord $model
     * @return Icon|string
     * @throws Exception
     */
    public static function getByModel(ContentActiveRecord $model)
    {
        if(!$model->content->isArchived()) {
            return '';
        }

        return static::get(static::ICON_NAME)
            ->tooltip(Yii::t('ContentModule.base', 'Archived'), Yii::t('ContentModule.aria', 'This content is archived'));
    }

}
