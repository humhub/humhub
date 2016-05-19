<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\widgets\BaseSidebar;

/**
 * ProfileSidebar implements the sidebar for the user profiles.
 * 
 * @since 0.5
 * @author Luke
 */
class ProfileSidebar extends BaseSidebar
{

    /**
     * @var \humhub\modules\user\models\User the user this sidebar belongs to
     */
    public $user;

}

?>
