<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;

/**
 * MembershipButton shows various membership related buttons in space header. 
 *
 * @author luke
 * @since 0.11
 */
class MembershipButton extends Widget
{

    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $membership = $this->space->getMembership();

        return $this->render('membershipButton', array(
                    'space' => $this->space,
                    'membership' => $membership
        ));
    }

}
