<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\widgets;

use humhub\libs\Html;
use humhub\components\Widget;

/**
 * UserGroupList displays a comma separated list of user groups
 *
 * @since 1.2
 * @author Luke
 */
class UserGroupList extends Widget
{

    /**
     * @var \humhub\modules\user\models\User
     */
    public $user;

    /**
     * @var string Tag name for base HTML tag
     */
    public $tagName = 'small';

    /**
     * @var array the HTML Options fo the base Tag
     */
    public $htmlOptions = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$this->user->hasGroup()) {
            return;
        }

        $groupList = implode(', ', array_map(function($g) {
                    return Html::encode($g->name);
                }, $this->user->groups));

        return Html::tag($this->tagName, $groupList, $this->htmlOptions);
    }

}
