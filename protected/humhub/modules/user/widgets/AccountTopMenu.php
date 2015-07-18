<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use humhub\models\Setting;

/**
 * AccountTopMenu Widget
 *
 * @author luke
 */
class AccountTopMenu extends \yii\base\Widget
{

    public function run()
    {
        $user = Yii::$app->user->getIdentity();

        $showUserApprovals = false;
        if (Setting::Get('needApproval', 'authentication_internal') && $user->canApproveUsers()) {
            $showUserApprovals = true;
        }

        return $this->render('accountTopMenu', [
                    'showUserApprovals' => $showUserApprovals,
                    'user' => $user
        ]);
    }

}
