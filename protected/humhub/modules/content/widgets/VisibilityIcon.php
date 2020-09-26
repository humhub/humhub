<?php


namespace humhub\modules\content\widgets;


use humhub\libs\Html;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\models\User;
use Yii;

/**
 * Class VisibilityIcon
 * @package humhub\modules\content\widgets
 * @since 1.7
 */
class VisibilityIcon extends Icon
{
    const ICON_PUBLIC = 'globe';
    const ICON_GROUP = 'users';
    const ICON_PRIVATE = 'lock';

    public static function getByModel(ContentActiveRecord $model)
    {
        return static::get(static::getVisibilityIcon($model))->tooltip(static::getVisibilityTitle($model));
    }

    private static function getVisibilityIcon(ContentActiveRecord $model)
    {
        if($model->content->container instanceof User && $model->content->isPrivate() && !Yii::$app->getModule('friendship')->settings->get('enable')) {
            return static::ICON_PRIVATE;
        }

        return $model->content->isPublic() ? static::ICON_PUBLIC : static::ICON_GROUP;
    }

    private static function getVisibilityTitle(ContentActiveRecord $model)
    {
        $container = $model->content->container;

        if($model->content->isPublic()) {
            // TODO better guest mode distinction
            return Yii::t('ContentModule.base', 'Can be seen by everyone');
        }


        if(!$container) { // private global
            return Yii::t('ContentModule.base', 'Can be seen by everyone.');
        }

        if($model->content->container instanceof Space) {
            // TODO more specific message for guest mode?
            return Yii::t('ContentModule.base', 'Can be seen by all space members');
        }

        if($model->content->container instanceof User) {
            $iamAuthor =  $model->content->createdBy->is(Yii::$app->user->getIdentity());
            $isMyProfile =  $model->content->container->is(Yii::$app->user->getIdentity());

            if(Yii::$app->getModule('friendship')->settings->get('enable')) {
                return $isMyProfile
                    ?  Yii::t('ContentModule.base', 'Can only be seen by you and your friends')
                    : Yii::t('ContentModule.base', 'Can only be seen by you and friends of {displayName}',
                        ['displayName' => Html::encode($model->content->container->getDisplayName())]);
            }

            // Private no friendships
            if($isMyProfile) {
                return $iamAuthor
                    ?  Yii::t('ContentModule.base', 'Can only be seen by you') // My own private profile post
                    :  Yii::t('ContentModule.base', 'Can be seen by you and {displayName}',
                        ['displayName' => Html::encode($model->content->createdBy->getDisplayName())]); // Someone posted private content on my profile
            }

            // My private content on another users profile
            return Yii::t('ContentModule.base', 'Can be seen by you and {displayName}', ['displayName' => Html::encode($model->content->container->getDisplayName())]);
        }

        return '';
    }

}
