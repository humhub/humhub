<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\widgets;

use humhub\libs\Html;
use yii\helpers\Url;
use humhub\components\Widget;

/**
 * UserTagList displays the user tags on the directory page
 *
 * @since 1.2
 * @author Luke
 */
class UserTagList extends Widget
{

    /**
     * @var \humhub\modules\user\models\User
     */
    public $user;

    /**
     * @var int number of max. displayed tags
     */
    public $maxTags = 5;

    /**
     * @inheritdoc
     */
    public function run()
    {

        $tags = $this->user->getTags();

        $count = count($tags);

        if ($count === 0) {
            return;
        } elseif ($count > $this->maxTags) {
            $tags = array_slice($tags, 0, $this->maxTags);
        }

        $html = '';
        foreach ($tags as $tag) {
            $html .= Html::a(Html::encode($tag), Url::to(['/directory/directory/members', 'keyword' => $tag]), ['class' => 'label label-default']) . "&nbsp";
        }

        return $html;
    }

}
