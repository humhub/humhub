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

        $data = '';
        $badge = '';

        /*if ($this->isMember && $this->space->isSpaceOwner()) {
            $badge = '<i class="fa fa-key badge-space pull-right type tt" title="' . Yii::t('SpaceModule.widgets_views_spaceChooserItem', 'Owner') . '" aria-hidden="true"></i>';
            $data = 'data-space-owner';
        }*/
        
        if($this->isMember) {
            $badge = '<i class="fa fa-users badge-space pull-right type tt" title="' . Yii::t('SpaceModule.widgets_views_spaceChooserItem', 'Member') . '" aria-hidden="true"></i>';
            $data = 'data-space-member';
        } else if($this->isFollowing) {
            $badge = '<i class="fa fa-star badge-space pull-right type tt" title="' . Yii::t('SpaceModule.widgets_views_spaceChooserItem', 'Following') . '" aria-hidden="true"></i>';
            $data = 'data-space-following';
        } else {
            $data = 'data-space-none';
        }

        return $this->render('spaceChooserItem', [
                    'space' => $this->space,
                    'updateCount' => $this->updateCount,
                    'visible' => $this->visible,
                    'badge' => $badge,
                    'data' => $data
        ]);
    }

}
