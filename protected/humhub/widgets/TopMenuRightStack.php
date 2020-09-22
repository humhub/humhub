<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;
use humhub\modules\user\components\User;

/**
 * TopMenuRightStackWidget holds items like search (right part)
 *
 * @since 0.6
 * @author Luke
 */
class TopMenuRightStack extends BaseStack
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        // Don't show stack if guest access is disabled and user is not logged in
        if (Yii::$app->user->isGuest && !User::isGuestAccessEnabled()) {
            return;
        }

        return parent::run();
    }

}
