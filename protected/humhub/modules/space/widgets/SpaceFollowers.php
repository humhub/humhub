<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\space\models\Space;
use yii\base\Widget;

/**
 * SpaceFollowers lists all followers of the Space
 *
 * @package humhub.modules_core.space.widget
 * @since 1.10.0
 * @author Luke
 */
class SpaceFollowers extends Widget
{

    /**
     * @var Space
     */
    public $space;

    public function run()
    {
        $followersQuery = $this->space->getFollowersQuery();

        $totalFollowerCount = $followersQuery->count();
        if (!$totalFollowerCount) {
            return '';
        }

        return $this->render('spaceFollowers', [
            'followers' => $followersQuery->limit(16)->all(),
            'totalFollowerCount' => $totalFollowerCount,
        ]);
    }

}
