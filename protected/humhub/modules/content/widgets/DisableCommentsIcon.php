<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\ui\icon\widgets\Icon;
use Yii;

/**
 * Can be used to create an icon with information about the comments status(disabled/enabled) of a content model.
 * The icon will be provided with a tooltip containing more detailed information about who is able to view this content.
 *
 * Usage:
 *
 * ```php
 * <?= DisableCommentsIcon::getByModel($model) ?>
 * ```
 *
 * @package humhub\modules\content\widgets
 * @since 1.10
 */
class DisableCommentsIcon extends Icon
{
    /**
     * Icon name used for content with disabled comments
     */
    const ICON_DISABLED = 'comment-o';

    /**
     * Icon name used for content with enabled comments
     */
    const ICON_ENABLED = 'comment';

    /**
     * Returns a comments status icon with tooltip for the given $model.
     *
     * @param ContentActiveRecord $model
     * @return string
     * @throws \Throwable
     */
    public static function getByModel(ContentActiveRecord $model, bool $displayEnabledIcon = false): string
    {
        if (!$displayEnabledIcon && !$model->content->isDisabledComments()) {
            return '';
        }

        return static::get(static::getCommentsStatusIcon($model))->tooltip(static::getCommentsStatusIconTitle($model));
    }

    /**
     * Returns a comments status icon name for the given $model.
     *
     * @param ContentActiveRecord $model
     * @return string
     */
    private static function getCommentsStatusIcon(ContentActiveRecord $model): string
    {
        return $model->content->isDisabledComments() ? static::ICON_DISABLED : static::ICON_ENABLED;
    }

    /**
     * Determines the tooltip text for the given $model.
     *
     * @param ContentActiveRecord $model
     * @return string
     * @throws \Throwable
     */
    private static function getCommentsStatusIconTitle(ContentActiveRecord $model): string
    {
        return $model->content->isDisabledComments()
            ? Yii::t('ContentModule.base', 'Comments are disabled for this content')
            : Yii::t('ContentModule.base', 'Comments are enabled for this content');
    }

}
