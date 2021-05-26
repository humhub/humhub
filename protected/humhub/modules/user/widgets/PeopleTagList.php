<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\libs\Html;
use yii\helpers\Url;
use humhub\components\Widget;

/**
 * PeopleTagList displays the user tags on the directory people page
 *
 * @since 1.2
 * @author Luke
 */
class PeopleTagList extends Widget
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
     * @var string Template for tags
     */
    public $template = '{tags}';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $html = '';

        $tags = $this->user->getTags();

        $count = count($tags);

        if ($count === 0) {
            return $html;
        }

        if ($count > $this->maxTags) {
            $tags = array_slice($tags, 0, $this->maxTags);
        }

        if (empty($tags)) {
            return $html;
        }

        foreach ($tags as $tag) {
            if (trim($tag) !== '') {
                $html .= Html::a(Html::encode($tag), Url::to(['/user/people', 'keyword' => trim($tag)]), ['class' => 'label label-default']) . '&nbsp';
            }
        }

        if ($html === '') {
            return $html;
        }

        return str_replace('{tags}', $html, $this->template);
    }

}
