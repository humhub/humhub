<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;
use humhub\modules\user\models\User;

/**
 * PeopleActionsButton shows directory options (following or friendship) for listed users
 * 
 * @since 1.9
 * @author Luke
 */
class PeopleCard extends Widget
{

    /**
     * @var string Card side: 'front' or 'back'
     */
    public $side = 'front';

    /**
     * @var User
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $view = $this->side === 'front' ? 'peopleCardFront' : 'peopleCardBack';

        return $this->render($view, [
            'user' => $this->user
        ]);
    }

}
