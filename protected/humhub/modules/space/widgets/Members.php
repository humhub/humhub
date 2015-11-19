<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use \yii\base\Widget;

/**
 * Shows space members in sidebar
 *
 * @author Luke
 * @since 0.5
 */
class Members extends Widget
{

    /**
     * @var int maximum members to display
     */
    public $maxMembers = 23;

    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $memberQuery = $this->space->getMemberships();
        $memberQuery->joinWith('user');
        $memberQuery->limit($this->maxMembers);
        $memberQuery->where(['user.status' => \humhub\modules\user\models\User::STATUS_ENABLED]);

        return $this->render('members', ['space' => $this->space, 'maxMembers' => $this->maxMembers, 'members' => $memberQuery->all()]);
    }

}

?>