<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\ui\widgets\CounterSet;
use humhub\modules\ui\widgets\CounterSetItem;
use Yii;
use yii\helpers\Url;

/**
 * Class HeaderCounterSet
 * @package humhub\modules\space\widgets
 */
class HeaderCounterSet extends CounterSet
{
    /**
     * @var Space
     */
    public $space;


    /**
     * @inheritdoc
     */
    public function init()
    {

        $postQuery = Content::find()
            ->where(['object_model' => Post::class, 'contentcontainer_id' => $this->space->contentContainerRecord->id])
            ->andWhere(['state' => Content::STATE_PUBLISHED]);
        $this->counters[] = new CounterSetItem([
            'label' => Yii::t('SpaceModule.base', 'Posts'),
            'value' => $postQuery->count(),
        ]);

        if (!$this->space->getAdvancedSettings()->hideMembers) {
            $this->counters[] = new CounterSetItem([
                'label' => Yii::t('SpaceModule.base', 'Members'),
                'value' => $this->space->getMemberListService()->getCount(),
                'url' => Yii::$app->user->isGuest ? null : '#',
                'linkOptions' => Yii::$app->user->isGuest ? [] : [
                    'data-action-click' => 'ui.modal.load',
                    'data-action-url' => Url::to(['/space/membership/members-list', 'container' => $this->space]),
                ],
            ]);
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('space');
        if (!$module->disableFollow && !$this->space->getAdvancedSettings()->hideFollowers) {
            $this->counters[] = new CounterSetItem([
                'label' => Yii::t('SpaceModule.base', 'Followers'),
                'value' => $this->space->getFollowersQuery()->count(),
                'url' => Yii::$app->user->isGuest ? null : '#',
                'linkOptions' => Yii::$app->user->isGuest ? [] : [
                    'data-action-click' => 'ui.modal.load',
                    'data-action-url' => Url::to(['/space/space/follower-list', 'container' => $this->space]),
                ],
            ]);
        }

        parent::init();
    }
}
