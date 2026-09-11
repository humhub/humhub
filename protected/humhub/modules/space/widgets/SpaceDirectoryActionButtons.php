<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\modules\space\models\Space;

/**
 * SpaceDirectoryActionButtons shows space directory buttons (following and membership)
 *
 * @since 1.9
 * @author Luke
 */
class SpaceDirectoryActionButtons extends Widget
{
    /**
     * @var Space
     */
    public $space;

    /**
     * @var string Template for buttons
     */
    public $template = '{buttons}';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $html = FollowButton::widget([
            'space' => $this->space,
        ]);

        $html .= MembershipButton::widget([
            'space' => $this->space,
            'buttonClass' => 'btn btn-accent btn-sm',
            'togglerClass' => 'btn btn-accent btn-sm',
            'pendingClass' => 'btn btn-sm btn-outline-accent',
            'memberClass' => 'btn btn-sm btn-outline-accent',
            // Unlike the space header, the directory has no controls menu to leave from.
            'showMemberState' => true,
        ]);

        if (trim($html) === '') {
            return '';
        }

        return str_replace('{buttons}', $html, $this->template);
    }

}
