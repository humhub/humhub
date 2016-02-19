<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\widgets;

use Yii;
use humhub\modules\friendship\models\Friendship;

/**
 * A panel which shows users friends in sidebar
 *
 * @since 1.1
 * @author luke
 */
class FriendsPanel extends \yii\base\Widget
{

    /**
     * @var User the target user 
     */
    public $user;

    /**
     * @var int limit of friends to display
     */
    public $limit = 30;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!Yii::$app->getModule('friendship')->getIsEnabled()) {
            return;
        }

        $querz = Friendship::getFriendsQuery($this->user);

        $totalCount = $querz->count();
        $friends = $querz->limit($this->limit)->all();

        return $this->render('friendsPanel', array(
                    'friends' => $friends,
                    'friendsShowLimit' => $this->limit,
                    'totalCount' => $totalCount,
                    'limit' => $this->limit,
                    'user' => $this->user,
        ));
    }

}
