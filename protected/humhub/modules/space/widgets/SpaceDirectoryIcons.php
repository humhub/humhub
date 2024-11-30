<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\modules\space\models\Space;
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

        $membersCountQuery = $this->space->getMemberListService()->getReadableQuery();
        $membersCount = Yii::$app->runtimeCache->getOrSet(__METHOD__ . Yii::$app->user->id . '-' . $this->space->id, function () use ($membersCountQuery) {
            return $membersCountQuery->count();
        });

        return $this->render('spaceDirectoryIcons', [
            'space' => $this->space,
            'membersCount' => Yii::$app->formatter->asShortInteger($membersCount),
            'canViewMembers' => $membership && $membership->isPrivileged(),
        ]);
    }

}
