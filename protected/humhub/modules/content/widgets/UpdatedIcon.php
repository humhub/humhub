<?php


namespace humhub\modules\content\widgets;


use DateTime;
use humhub\modules\ui\icon\widgets\Icon;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Can be used to render an update icon for a given date.
 *
 * Usage:
 *
 * ```php
 * <?= UpdatedIcon::updateIcon($model->updated_at) ?>
 * ```
 * @package humhub\modules\content\widgets
 * @since 1.7
 */
class UpdatedIcon extends Icon
{
    /**
     * The icon name used for rendering
     */
    const ICON_NAME = 'clock-o';

    /**
     * Creates an updated icon with tooltip containing a formatted date
     *
     * @param $updateDate int|string|DateTime $value the value to be formatted
     * @return Icon
     * @throws InvalidConfigException
     */
    public static function getByDated($updateDate)
    {
        return static::get(static::ICON_NAME)
            ->tooltip(Yii::t('ContentModule.base', 'Last updated {time}', ['time' => Yii::$app->formatter->asDate($updateDate, 'medium') . ' - ' . Yii::$app->formatter->asTime($updateDate, 'short')]));
    }

}
