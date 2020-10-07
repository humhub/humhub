<?php


namespace humhub\modules\content\widgets;


use humhub\libs\Html;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
use Yii;

/**
 * Can be used to create an icon with information about the visibility of a content model.
 * The icon will be provided with a tooltip containing more detailed information about who is able to view this content.
 *
 * Usage:
 *
 * ```php
 * <?= VisibilityIcon::getByModel($model) ?>
 * ```
 *
 * @package humhub\modules\content\widgets
 * @since 1.7
 */
class VisibilityIcon extends Icon
{
    /**
     * Icon name used for all public content
     */
    const ICON_PUBLIC = 'globe';

    /**
     * Icon name used for group level content e.g. space / friends
     */
    const ICON_GROUP = 'users';

    /**
     * Icon name used for private content profile content (without friendships active)
     */
    const ICON_PRIVATE = 'lock';

    /**
     * Returns an visibility icon with tooltip for the given $model.
     *
     * @param ContentActiveRecord $model
     * @return Icon
     * @throws \Throwable
     */
    public static function getByModel(ContentActiveRecord $model)
    {
        return static::get(static::getVisibilityIcon($model))->tooltip(static::getVisibilityTitle($model));
    }

    /**
     * Returns the visibility icon name for the given $model.
     *
     * @param ContentActiveRecord $model
     * @return string
     */
    private static function getVisibilityIcon(ContentActiveRecord $model)
    {
        if($model->content->container instanceof User && $model->content->isPrivate() && !Yii::$app->getModule('friendship')->settings->get('enable')) {
            return static::ICON_PRIVATE;
        }

        return $model->content->isPublic() ? static::ICON_PUBLIC : static::ICON_GROUP;
    }

    /**
     * Determines the tooltip text for the given $model.
     *
     * @param ContentActiveRecord $model
     * @return string
     * @throws \Throwable
     */
    private static function getVisibilityTitle(ContentActiveRecord $model)
    {
        $container = $model->content->container;

        if($model->content->isPublic()) {
            return static::getPublicVisibilityText();
        }


        if(!$container) { // private global
            return Yii::t('ContentModule.base', 'Visible to all signed in users');
        }

        if($model->content->container instanceof Space) {
            return Yii::t('ContentModule.base', 'Visible to all Space members');
        }

        if($model->content->container instanceof User) {
            $iamAuthor =  $model->content->createdBy->is(Yii::$app->user->getIdentity());
            $isMyProfile =  $model->content->container->is(Yii::$app->user->getIdentity());

            if(Yii::$app->getModule('friendship')->settings->get('enable')) {
                return $isMyProfile
                    ?  Yii::t('ContentModule.base', 'Visible to your friends')
                    : Yii::t('ContentModule.base', 'Visible to friends of {displayName}',
                        ['displayName' => Html::encode($model->content->container->getDisplayName())]);
            }

            // Private no friendships
            if($isMyProfile) {
                return $iamAuthor
                    ?  Yii::t('ContentModule.base', 'Visible only to you') // My own private profile post
                    :  Yii::t('ContentModule.base', 'Visible to you and {displayName}',
                        ['displayName' => Html::encode($model->content->createdBy->getDisplayName())]); // Someone posted private content on my profile
            }

            // My private content on another users profile
            return Yii::t('ContentModule.base', 'Visible to you and {displayName}', ['displayName' => Html::encode($model->content->container->getDisplayName())]);
        }

        return '';
    }

    private static function getPublicVisibilityText()
    {
        return AuthHelper::isGuestAccessEnabled()
            ? Yii::t('ContentModule.base', 'Visible also to unregistered users')
            : Yii::t('ContentModule.base', 'Visible to all signed in users');
    }

}
