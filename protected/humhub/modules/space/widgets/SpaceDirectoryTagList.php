<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\libs\Html;
use yii\helpers\Url;
use humhub\components\Widget;

/**
 * SpaceDirectoryTagList displays the space tags on the directory spaces page
 *
 * @since 1.9
 * @author Luke
 */
class SpaceDirectoryTagList extends Widget
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
     * @var string Template for tags
     */
    public $template = '{tags}';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $html = '';

        $tags = $this->space->getTags();

        $count = count($tags);

        if ($count === 0) {
            return $html;
        } elseif ($count > $this->maxTags) {
            $tags = array_slice($tags, 0, $this->maxTags);
        }

        $html = '';
        foreach ($tags as $tag) {
            if (trim($tag) !== '') {
                $html .= Html::a(Html::encode($tag), Url::to(['/space/spaces', 'keyword' => trim($tag)]), ['class' => 'label label-default']) . "&nbsp";
            }
        }

        if ($html === '') {
            return $html;
        }

        return str_replace('{tags}', $html, $this->template);
    }

}
