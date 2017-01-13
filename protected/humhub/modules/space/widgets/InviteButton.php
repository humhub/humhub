<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
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
        
        return Html::a('<i class="fa fa-plus"></i> '. Yii::t('SpaceModule.widgets_views_inviteButton', 'Invite'), $this->space->createUrl('/space/membership/invite'), ['class' => 'btn btn-primary', 'data-target' => '#globalModal']);
    }

}
