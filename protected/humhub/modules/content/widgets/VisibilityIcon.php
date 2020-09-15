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

    public static function getByModel(ContentActiveRecord $model, $options = [])
    {
        return static::get(static::getVisibilityIcon($model), static::getVisibilityIconOptions($model, $options));
    }

    private static function getVisibilityIcon(ContentActiveRecord $model)
    {
        if($model->content->container instanceof User && $model->content->isPrivate() && !Yii::$app->getModule('friendship')->settings->get('enable')) {
            return static::ICON_PRIVATE;
        }

        return $model->content->isPublic() ? static::ICON_PUBLIC : static::ICON_GROUP;
    }

    private static function getVisibilityIconOptions(ContentActiveRecord $model, $options = [])
    {
        if(!isset($options['htmlOptions'])) {
            $options['htmlOptions'] = [];
        }

        Html::addCssClass($options['htmlOptions'], 'tt');
        $options['htmlOptions']['title'] = static::getVisibilityTitle($model);

        return $options;
    }

    private static function getVisibilityTitle(ContentActiveRecord $model)
    {
        $container = $model->content->container;

        if($model->content->isPublic()) {
            // TODO better guest mode distinction
            return Yii::t('ContentModule.base', 'Can be seen by everyone');
        }


        if(!$container) { // private global
            return Yii::t('ContentModule.base', 'Can be seen by all members of this network.');
        }

        if($model->content->container instanceof Space) {
            // TODO more specific message for guest mode?
            return Yii::t('ContentModule.base', 'Can be seen by all space members');
        }

        if($model->content->container instanceof User) {
            $isFriendShipActive = Yii::$app->getModule('friendship')->settings->get('enable');

            return $isFriendShipActive
                ? Yii::t('ContentModule.base', 'Can be seen by you and your friends')
                : Yii::t('ContentModule.base', 'Can only be seen by you');
        }

        return '';
    }

}
