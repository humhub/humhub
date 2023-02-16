<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\activity\models\Activity;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\ui\widgets\CounterSetItem;
use humhub\modules\ui\widgets\CounterSet;
use Yii;
use yii\helpers\Url;

class HeaderCounterSet extends CounterSet
{

    public ?Space $space = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->space) {
            return;
        }

        $this->addContentCounter();

        if (!$this->space->getAdvancedSettings()->hideMembers) {
            $this->addMemberCounter();
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('space');
        if (!$module->disableFollow && !$this->space->getAdvancedSettings()->hideFollowers) {
            $this->addFollowerCounter();
        }

        parent::init();
    }

    private function addContentCounter()
    {
        $postQuery = Content::find()->where(['contentcontainer_id' => $this->space->contentContainerRecord->id])
            ->andWhere(['!=', 'object_model', Activity::class]);
        $contentCount = $postQuery->count();
        $this->counters[] = new CounterSetItem([
            'label' => Yii::t(
                'SpaceModule.base', '{count,plural,=0{Contents} =1{Content} other{Contents}}',
                ['count' => $contentCount]
            ),
            'value' => $contentCount
        ]);
    }

    private function addMemberCounter()
    {
        $memberCount = Membership::getSpaceMembersQuery($this->space)->active()->visible()->count();
        $this->counters[] = new CounterSetItem([
            'label' => Yii::t(
                'SpaceModule.base', '{count,plural,=0{Members} =1{Member} other{Members}}',
                ['count' => $memberCount]
            ),
            'value' => $memberCount,
            'url' => Yii::$app->user->isGuest ? null : '#',
            'linkOptions' => Yii::$app->user->isGuest ? [] : [
                'data-action-click' => 'ui.modal.load',
                'data-action-url' => Url::to(['/space/membership/members-list', 'container' => $this->space])
            ]
        ]);
    }

    private function addFollowerCounter()
    {
        $followerCount = $this->space->getFollowersQuery()->count();
        $this->counters[] = new CounterSetItem([
            'label' => Yii::t('SpaceModule.base', '{count,plural,=0{Followers} =1{Follower} other{Followers}}', ['count' => $followerCount]),
            'value' => $followerCount,
            'url' => Yii::$app->user->isGuest ? null : '#',
            'linkOptions' => Yii::$app->user->isGuest ? [] : [
                'data-action-click' => 'ui.modal.load',
                'data-action-url' => Url::to(['/space/space/follower-list', 'container' => $this->space])
            ]
        ]);
    }

}
