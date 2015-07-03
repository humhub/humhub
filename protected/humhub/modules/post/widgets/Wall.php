<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\widgets;

/**
 * Description of Wall
 *
 * @author luke
 */
class Wall extends \yii\base\Widget
{

    /**
     * The post object
     *
     * @var Post
     */
    public $post;

    /**
     * Indicates the post was just edited
     *
     * @var boolean
     */
    public $justEdited = false;

    public function run()
    {
        return $this->render('wall', array('post' => $this->post, 'justEdited' => $this->justEdited));
    }

}
