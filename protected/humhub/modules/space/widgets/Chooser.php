<?php

namespace humhub\modules\space\widgets;

use Yii;
use humhub\components\Widget;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\Follow;
use yii\helpers\Html;

/**
 * Created by PhpStorm.
 * User: Struppi
 * Date: 17.12.13
 * Time: 12:49
 */
class Chooser extends Widget
{

    public static function getSpaceResult($space, $withChooserItem = true, $options = [])
    {
        $spaceInfo = [];
        $spaceInfo['guid'] = $space->guid;
        $spaceInfo['title'] = Html::encode($space->name);
        $spaceInfo['tags'] = Html::encode($space->tags);
        $spaceInfo['image'] = Image::widget(['space' => $space, 'width' => 24]);
        $spaceInfo['link'] = $space->getUrl();

        if ($withChooserItem) {
            $options = array_merge(['space' => $space, 'isMember' => false, 'isFollowing' => false], $options);
            $spaceInfo['output'] = \humhub\modules\space\widgets\SpaceChooserItem::widget($options);
        }

        return $spaceInfo;
    }

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        return $this->render('spaceChooser', [
                    'currentSpace' => $this->getCurrentSpace(),
                    'canCreateSpace' => $this->canCreateSpace(),
                    'memberships' => $this->getMemberships(),
                    'followSpaces' => $this->getFollowSpaces()
        ]);
    }

    protected function getFollowSpaces()
    {
        if (!Yii::$app->user->isGuest) {
            return Follow::getFollowedSpacesQuery(Yii::$app->user->getIdentity())->all();
        }
    }

    protected function getMemberships()
    {
        if (!Yii::$app->user->isGuest) {
            return Membership::findByUser(Yii::$app->user->getIdentity())->all();
        }
    }

    protected function canCreateSpace()
    {
        return (Yii::$app->user->permissionmanager->can(new CreatePublicSpace) || Yii::$app->user->permissionmanager->can(new CreatePrivateSpace()));
    }

    protected function getCurrentSpace()
    {
        if (Yii::$app->controller instanceof \humhub\modules\content\components\ContentContainerController) {
            if (Yii::$app->controller->contentContainer !== null && Yii::$app->controller->contentContainer instanceof \humhub\modules\space\models\Space) {
                return Yii::$app->controller->contentContainer;
            }
        }

        return null;
    }

}

?>