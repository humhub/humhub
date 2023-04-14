<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;

/**
 * SpaceDirectoryIcons shows footer icons for spaces cards
 *
 * @since 1.9
 * @author Luke
 */
class SpaceDirectoryIcons extends Widget
{

    /**
     * @var Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->space->getAdvancedSettings()->hideMembers) {
            return '';
        }

        $membership = $this->space->getMembership();
        $membersCountQuery = Membership::getSpaceMembersQuery($this->space)->active();
        if (Yii::$app->user->isGuest) {
            $membersCountQuery->andWhere(['!=', 'user.visibility', User::VISIBILITY_HIDDEN]);
        } else {
            $membersCountQuery->visible();
        }

        return $this->render('spaceDirectoryIcons', [
            'space' => $this->space,
            'membersCount' => Yii::$app->formatter->asShortInteger($membersCountQuery->count()),
            'canViewMembers' => $membership && $membership->isPrivileged(),
        ]);
    }

}
