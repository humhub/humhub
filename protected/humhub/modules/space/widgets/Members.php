<?php

namespace humhub\modules\space\widgets;

use Yii;
use \yii\base\Widget;

/**
 * This widget will added to the sidebar, when on admin area
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class Members extends Widget
{

    public $maxMembers = 23;
    public $space;

    public function run()
    {
        $memberQuery = $this->space->getMemberships();
        $memberQuery->joinWith('user');
        $memberQuery->limit($this->maxMembers);
        $memberQuery->where(['user.status' => \humhub\modules\user\models\User::STATUS_ENABLED]);

        $showApplicants = false;
        $applicants = [];

        if ($this->space->isAdmin() && $this->space->getApplicants()->count() != 0) {
            $showApplicants = true;
            $applicants = $this->space->getApplicants()->limit('15')->all();
        }

        return $this->render('spaceMembers', ['space' => $this->space, 'members' => $memberQuery->all(), 'showApplicants' => $showApplicants, 'applicants' => $applicants]);
    }

}

?>