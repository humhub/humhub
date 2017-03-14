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
 * SpaceTagList displays the user tags on the directory page
 *
 * @since 1.2
 * @author Luke
 */
class SpaceTagList extends Widget
{

    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    /**
     * @var int number of max. displayed tags
     */
    public $maxTags = 5;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $tags = $this->space->getTags();

        $count = count($tags);

        if ($count === 0) {
            return;
        } elseif ($count > $this->maxTags) {
            $tags = array_slice($tags, 0, $this->maxTags);
        }

        $html = '';
        foreach ($tags as $tag) {
            $html .= Html::a(Html::encode($tag), Url::to(['/directory/directory/spaces', 'keyword' => $tag]), ['class' => 'label label-default']) . "&nbsp";
        }

        return $html;
    }

}
