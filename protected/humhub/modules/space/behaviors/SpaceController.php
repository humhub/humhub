<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\behaviors;

use Yii;
use yii\base\Behavior;
use yii\helpers\Json;
use yii\web\HttpException;
use humhub\libs\Html;
use humhub\modules\space\widgets\Image;
use humhub\modules\space\models\Space;
use humhub\components\Controller;

/**
 * SpaceController Behavior
 * 
 * In Space scopes, this behavior will automatically attached to a contentcontainer controller.
 * 
 * @see Space::controllerBehavior
 * @see \humhub\modules\contentcontainer\components\Controller
 * @property \humhub\modules\contentcontainer\components\Controller $owner the controller
 */
class SpaceController extends Behavior
{

    /**
     * @var humhub\modules\space\models\Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        parent::attach($owner);

        if (!$this->owner->contentContainer instanceof Space) {
            throw new \yii\base\InvalidValueException('Invalid contentcontainer type of controller.');
        }

        $this->space = $this->owner->contentContainer;
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction'
        ];
    }

    public function beforeAction($action)
    {
        $this->updateLastVisit();

        if (Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess') && Yii::$app->user->isGuest && $this->space->visibility != Space::VISIBILITY_ALL) {
            throw new HttpException(401, Yii::t('SpaceModule.behaviors_SpaceControllerBehavior', 'You need to login to view contents of this space!'));
        }

        if ($this->getMembership() === null && $this->space->visibility == Space::VISIBILITY_NONE && !Yii::$app->user->isAdmin()) {
            throw new HttpException(404, Yii::t('SpaceModule.behaviors_SpaceControllerBehavior', 'Space is invisible!'));
        }

        $this->owner->subLayout = "@humhub/modules/space/views/space/_layout";
        $this->owner->prependPageTitle($this->space->name);

        if (Yii::$app->request->isPjax || !Yii::$app->request->isAjax) {
            $options = [
                'guid' => $this->owner->contentContainer->guid,
                'name' => Html::encode($this->owner->contentContainer->name),
                'archived' => $this->space->isArchived(),
                'image' => Image::widget([
                    'space' => $this->owner->contentContainer,
                    'width' => 32,
                    'htmlOptions' => [
                        'class' => 'current-space-image',
                    ],
                ]),
            ];

            $this->owner->view->registerJs('humhub.modules.space.setSpace(' . Json::encode($options) . ', ' .
                    Json::encode(Yii::$app->request->isPjax) . ')');
        }
    }

    protected function updateLastVisit()
    {
        $membership = $this->getMembership();
        if ($membership != null) {
            $membership->updateLastVisit();
        }
    }

    protected function getMembership()
    {
        // ToDo: Cache
        return $this->space->getMembership(Yii::$app->user->id);
    }

    public function getSpace()
    {
        return $this->space;
    }

}

?>
