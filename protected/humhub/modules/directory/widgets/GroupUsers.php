<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\widgets;

use Yii;
use yii\helpers\Url;
use humhub\libs\Html;
use humhub\modules\user\models\Group;
use humhub\components\Widget;

/**
 * GroupUsers shows users of a group
 *
 * @since 1.2
 * @author Luke
 */
class GroupUsers extends Widget
{

    /**
     * @var Group
     */
    public $group;

    /**
     * @var int maximum number of users to display
     */
    public $maxUsers = 30;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $users = $this->group->getUsers()->active()->limit($this->maxUsers + 1)->joinWith('profile')->orderBy(['profile.lastname' => SORT_ASC])->all();


        if (count($users) === 0) {
            return Html::tag('small', Yii::t('DirectoryModule.base', 'This group has no members yet.'));
        }

        $showMoreUrl = '';
        if (count($users) > $this->maxUsers) {
            array_pop($users);
            $showMoreUrl = Url::to(['/directory/directory/members', 'keyword' => '', 'groupId' => $this->group->id]);
        }

        return $this->render('groupUsers', [
                    'group' => $this->group,
                    'users' => $users,
                    'showMoreUrl' => $showMoreUrl
        ]);
    }

}
