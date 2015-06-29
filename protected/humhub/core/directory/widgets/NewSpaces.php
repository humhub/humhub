<?php

namespace humhub\core\directory\widgets;

use humhub\core\space\models\Space;
use humhub\core\space\models\Membership;

/**
 * Shows newly created spaces as sidebar widget
 *
 * @package humhub.modules_core.directory.widgets
 * @since 0.11
 * @author Luke
 */
class NewSpaces extends \yii\base\Widget
{

    public $showMoreButton = false;

    /**
     * Executes the widgets
     */
    public function run()
    {

        $query = Space::find();
        $query->leftJoin('space_membership', 'space.id=space_membership.space_id AND space_membership.user_id=:userId', [':userId' => \Yii::$app->user->id]);
        $query->andWhere([
            '!=', 'space.visibility', Space::VISIBILITY_NONE,
        ]);
        $query->andWhere([
            'space_membership.status' => Membership::STATUS_MEMBER,
        ]);
        $query->limit(10);
        $query->orderBy('created_at DESC');

        return $this->render('newSpaces', array(
                    'newSpaces' => $query->all(),
                    'showMoreButton' => $this->showMoreButton
        ));
    }

}

?>
