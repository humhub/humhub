<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\ui\widgets\CounterSetItem;
use humhub\modules\ui\widgets\CounterSet;
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
            ->where(['object_model' => Post::class, 'contentcontainer_id' => $this->space->contentContainerRecord->id]);
        $this->counters[] = new CounterSetItem([
            'label' => Yii::t('SpaceModule.base', 'Posts'),
            'value' => $postQuery->count()
        ]);

        $this->counters[] = new CounterSetItem([
            'label' => Yii::t('SpaceModule.base', 'Members'),
            'value' => Membership::getSpaceMembersQuery($this->space)->active()->visible()->count(),
            'url' => (Yii::$app->user->isGuest) ? null : Url::to(['/space/membership/members-list', 'container' => $this->space]),
            'linkOptions' => ['data-action-click' => 'ui.modal.load']

        ]);

        if (!Yii::$app->getModule('space')->disableFollow) {
            $this->counters[] = new CounterSetItem([
                'label' => Yii::t('SpaceModule.base', 'Followers'),
                'value' => $this->space->getFollowerCount(),
                'url' => (Yii::$app->user->isGuest) ? null :  Url::to(['/space/space/follower-list', 'container' => $this->space]),
                'linkOptions' => ['data-action-click' => 'ui.modal.load']
            ]);
        }

        parent::init();
    }

}
