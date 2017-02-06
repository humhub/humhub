<?php

namespace humhub\modules\space\widgets;

use Yii;
use humhub\components\Widget;

/**
 * Used to render a single space chooser result.
 * 
 */
class SpaceChooserItem extends Widget
{

    /**
     * @var string
     */
    public $space;

    /**
     * @var integer
     */
    public $updateCount = 0;

    /**
     * @var boolean
     */
    public $visible = true;

    /**
     * If true the item will be marked as a following space
     * @var boolean
     */
    public $isFollowing = false;

    /**
     * If true the item will be marked as a member space
     * @var string
     */
    public $isMember = false;

    public function run()
    {

        $data = $this->getDataAttribute();
        $badge = $this->getBadge();

        return $this->render('spaceChooserItem', [
                    'space' => $this->space,
                    'updateCount' => $this->updateCount,
                    'visible' => $this->visible,
                    'badge' => $badge,
                    'data' => $data
        ]);
    }

    public function getBadge()
    {
        if ($this->isMember) {
            return '<i class="fa fa-users badge-space pull-right type tt" title="' . Yii::t('SpaceModule.widgets_spaceChooserItem', 'You are a member of this space') . '" aria-hidden="true"></i>';
        } else if ($this->isFollowing) {
            return '<i class="fa fa-star badge-space pull-right type tt" title="' . Yii::t('SpaceModule.widgets_spaceChooserItem', 'You are following this space') . '" aria-hidden="true"></i>';
        } else if ($this->space->isArchived()) {
            return '<i class="fa fa-history badge-space pull-right type tt" title="' . Yii::t('SpaceModule.widgets_spaceChooserItem', 'This space is archived') . '" aria-hidden="true"></i>';
        }
    }
    
    public function getDataAttribute()
    {
        if ($this->isMember) {
            return 'data-space-member';
        } else if ($this->isFollowing) {
            return 'data-space-following';
        } else if ($this->space->isArchived()) {
            return 'data-space-archived';
        } else {
            return 'data-space-none';
        }
    }
}
