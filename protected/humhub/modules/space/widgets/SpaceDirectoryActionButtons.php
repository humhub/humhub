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
            'variant' => MembershipButton::VARIANT_DIRECTORY,
        ]);

        if (trim($html) === '') {
            return '';
        }

        return str_replace('{buttons}', $html, $this->template);
    }

}
