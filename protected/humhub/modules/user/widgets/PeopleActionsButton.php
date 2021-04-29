<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;

/**
 * PeopleActionsButton shows directory options (following or friendship) for listed users
 * 
 * @since 1.9
 * @author Luke
 */
class PeopleActionsButton extends Widget
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
        return $this->render('peopleActionsButton', [
            'user' => $this->user
        ]);
    }

}
