<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\widgets;

use humhub\components\Widget;

/**
 * MemberActionsButton shows directory options (following or friendship) for listed users
 * 
 * @since 1.2
 * @author Luke
 */
class MemberActionsButton extends Widget
{

    /**
     * @var \humhub\modules\user\models\User
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('memberActionsButton', [
                    'user' => $this->user
        ]);
    }

}
