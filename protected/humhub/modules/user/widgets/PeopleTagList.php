<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;
use humhub\modules\user\models\User;
use humhub\widgets\bootstrap\Button;

/**
 * PeopleTagList displays the user tags on the directory people page
 *
 * @since 1.2
 * @author Luke
 */
class PeopleTagList extends Widget
{
    /**
     * @var User
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
                $html .= Button::asBadge($tag, 'light')->link(['/user/people', 'keyword' => trim($tag)]) . '&nbsp';
            }
        }

        if ($html === '') {
            return $html;
        }

        return str_replace('{tags}', $html, $this->template);
    }

}
