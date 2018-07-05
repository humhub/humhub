<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2014 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * SpaceInviteButtonWidget
 *
 * @author luke
 * @package humhub.modules_core.space.widgets
 * @since 0.11
 */
class InviteButton extends Widget
{

    public $space;

    public function run()
    {
        if (!$this->space->canInvite()) {
            return;
        }
        
        return $this->render('inviteButton', ['space' => $this->space]);
    }

}
